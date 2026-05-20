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
        $products = Product::orderByDesc('is_recommended')
                           ->orderByDesc('rating')
                           ->take(12)
                           ->get()
                           ->map(fn ($p) =>
                               "{$p->name} ({$p->category}) - {$p->price_formatted}, rating {$p->rating}"
                               . ($p->is_recommended ? ' [AI PICK]' : '')
                           )
                           ->join("\n");

        return <<<PROMPT
Kamu adalah AI asisten untuk SmartCatalog, sebuah aplikasi e-commerce berbasis Web 4.0 yang dibangun dengan Next.js 14, Laravel 11, MySQL, Redis, dan OpenRouter AI API.

Produk tersedia:
{$products}

Tugasmu:
- Bantu user temukan produk yang sesuai kebutuhan mereka
- Berikan rekomendasi personal berdasarkan konteks percakapan
- Jelaskan fitur teknis aplikasi (Next.js App Router, Laravel Sanctum, Redis Queue, OpenRouter) jika ditanya
- Berikan insight tentang tren, analitik, dan performa AI recommendation
- Selalu jawab dalam Bahasa Indonesia
- Gunakan **bold** untuk nama produk yang direkomendasikan
- Respons ringkas (2-3 paragraf max) dan actionable
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
