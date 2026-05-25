<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // OpenRouter Integration
    // Docs  : https://openrouter.ai/docs
    // OpenRouter is OpenAI-compatible — same /chat/completions endpoint format,
    // different base URL and Authorization header.
    // ─────────────────────────────────────────────────────────────────────────

    private string $baseUrl = 'https://openrouter.ai/api/v1';

    private function buildSystemPrompt(): string
    {
        // Group top products by category so the AI understands the breadth of the catalogue
        $byCategory = Product::orderByDesc('is_recommended')
            ->orderByDesc('rating')
            ->take(80) // wider window = richer AI context
            ->get()
            ->groupBy('category')
            ->map(fn ($items, $cat) =>
                "【{$cat}】\n" . $items->take(8)->map(fn ($p) =>
                    "  • {$p->name} — {$p->price_formatted}, ⭐{$p->rating} ({$p->rating_count} ulasan)"
                    . ($p->is_recommended ? ' 🏆 AI PICK' : '')
                    . ($p->stock <= 5 ? ' ⚠️ stok tipis' : '')
                )->join("\n")
            )->join("\n\n");

        $totalProducts   = Product::count();
        $categories      = Product::distinct()->pluck('category')->join(', ');
        $recommendedCount = Product::where('is_recommended', true)->count();
        $avgRating       = round(Product::avg('rating'), 2);

        return <<<PROMPT
Kamu adalah AI asisten cerdas untuk **SmartCatalog**, platform e-commerce Web 4.0 yang dibangun di atas:
• Frontend: Next.js 14 App Router + Tailwind CSS
• Backend: Laravel 11 + MySQL + Redis (cache & queue)
• AI Layer: OpenRouter AI (multi-model inference)

═══ KATALOG PRODUK ═══
Total: {$totalProducts} produk | Kategori: {$categories}
Produk direkomendasikan AI: {$recommendedCount} | Avg rating: {$avgRating}⭐

{$byCategory}
═══ END KATALOG ═══

PANDUAN RESPONS:
1. Rekomendasi personal — tanyakan kebutuhan/budget jika belum jelas
2. Gunakan **bold** untuk nama produk yang direkomendasikan
3. Sertakan harga dan rating saat menyebut produk spesifik
4. Tandai 🏆 untuk AI PICK dan ⚠️ untuk stok tipis
5. Untuk pertanyaan insight bisnis: berikan analisis tren kategori, performa rating, atau peluang cross-sell
6. Jika ditanya tentang teknis app (Next.js, Laravel, Redis, OpenRouter): jelaskan arsitekturnya
7. Selalu jawab dalam **Bahasa Indonesia** yang natural dan ramah
8. Respons ringkas dan actionable (maks 3 paragraf) — hindari daftar panjang kecuali diminta
9. Jika stok produk ⚠️ tipis, tambahkan urgensi pembelian dengan sopan

INSIGHT YANG BISA KAMU BERIKAN:
- "Produk terlaris/terpopuler berdasarkan rating dan review count"
- "Perbandingan harga antar kategori atau merek"
- "Cross-sell: 'Jika kamu suka X, kamu mungkin juga suka Y'"
- "Bundle rekomendasi: produk yang saling melengkapi"
- "Analisis value-for-money berdasarkan harga vs rating"
PROMPT;
    }

    public function stream(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'messages'           => 'required|array|min:1',
            'messages.*.role'    => 'required|in:user,assistant',
            'messages.*.content' => 'required|string|max:4000',
        ]);

        $userMessages = collect($validated['messages'])
            ->map(fn ($m) => ['role' => $m['role'], 'content' => $m['content']])
            ->values()
            ->toArray();

        // Prepend system message (OpenAI-compatible format)
        $messages = array_merge(
            [['role' => 'system', 'content' => $this->buildSystemPrompt()]],
            $userMessages
        );

        return new StreamedResponse(function () use ($messages) {
            $apiKey = config('services.openrouter.key');
            $model  = config('services.openrouter.model', 'meta-llama/llama-3.3-8b-instruct:free');
            $appUrl = config('app.url', 'http://localhost:8000');

            if (! $apiKey) {
                echo 'data: ' . json_encode(['error' => 'OPENROUTER_API_KEY not configured']) . "\n\n";
                flush();
                return;
            }

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type'  => 'application/json',
                // Required by OpenRouter — identifies your app on their dashboard
                'HTTP-Referer'  => $appUrl,
                'X-Title'       => 'SmartCatalog Web 4.0 Demo',
            ])
            ->withOptions(['stream' => true])
            ->post("{$this->baseUrl}/chat/completions", [
                'model'       => $model,
                'messages'    => $messages,
                'max_tokens'  => 800,
                'temperature' => 0.7,
                'stream'      => true,
            ]);

            $body = $response->toPsrResponse()->getBody();

            while (! $body->eof()) {
                // Read line by line
                $line = '';
                while (! $body->eof()) {
                    $char = $body->read(1);
                    if ($char === "\n") break;
                    $line .= $char;
                }

                $line = trim($line);
                if (! str_starts_with($line, 'data: ')) continue;

                $data = substr($line, 6);
                if ($data === '[DONE]') break;

                $json = json_decode($data, true);
                if (! $json) continue;

                // OpenAI-compatible SSE: choices[0].delta.content
                $text = $json['choices'][0]['delta']['content'] ?? null;
                if ($text !== null && $text !== '') {
                    echo 'data: ' . json_encode(['text' => $text]) . "\n\n";
                    flush();
                }

                // Token usage (sent in the last chunk)
                if (isset($json['usage'])) {
                    $tokens = ($json['usage']['prompt_tokens'] ?? 0)
                            + ($json['usage']['completion_tokens'] ?? 0);
                    echo 'data: ' . json_encode(['tokens' => $tokens]) . "\n\n";
                    flush();
                }
            }

            echo "data: [DONE]\n\n";
            flush();

        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection'        => 'keep-alive',
        ]);
    }
}
