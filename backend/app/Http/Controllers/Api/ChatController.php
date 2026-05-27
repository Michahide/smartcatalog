<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // OpenRouter Integration
    // Docs  : https://openrouter.ai/docs
    // Mirrors the behaviour of frontend/src/app/api/chat/route.ts exactly:
    //   • Accepts { messages, context? } — context is appended to system prompt
    //   • 30 s upstream connect timeout
    //   • 30 s stream read timeout (closes connection on stall)
    //   • Upstream error body forwarded as { error } SSE event
    //   • Logs [chat] debug lines matching the Next.js route
    //   • SSE output format: data: {"text":"…"} / data: {"tokens":N} / data: [DONE]
    // ─────────────────────────────────────────────────────────────────────────

    private string $baseUrl = 'https://openrouter.ai/api/v1';

    // ── System prompt (mirrors SYSTEM_PROMPT const + context injection) ──────

    private function buildSystemPrompt(?string $context = null): string
    {
        // Group top products by category — same logic as Next.js static list
        // but dynamic so the AI always sees the live catalogue.
        $byCategory = Product::orderByDesc('is_recommended')
            ->orderByDesc('rating')
            ->take(80)
            ->get()
            ->groupBy('category')
            ->map(fn ($items, $cat) =>
                "【{$cat}】\n" . $items->take(8)->map(fn ($p) =>
                    "  • {$p->name} — {$p->price_formatted}, ⭐{$p->rating} ({$p->rating_count} ulasan)"
                    . ($p->is_recommended ? ' 🏆 AI PICK' : '')
                    . ($p->stock <= 5     ? ' ⚠️ stok tipis' : '')
                )->join("\n")
            )->join("\n\n");

        $totalProducts    = Product::count();
        $categories       = Product::distinct()->pluck('category')->join(', ');
        $recommendedCount = Product::where('is_recommended', true)->count();
        $avgRating        = round(Product::avg('rating'), 2);

        $base = <<<PROMPT
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

        // Mirror: systemPrompt = context ? `${SYSTEM_PROMPT}\n\nKonteks tambahan: ${context}` : SYSTEM_PROMPT
        if ($context !== null && $context !== '') {
            $base .= "\n\nKonteks tambahan: {$context}";
        }

        return $base;
    }

    // ── POST /api/chat/stream ─────────────────────────────────────────────────

    public function stream(Request $request): StreamedResponse|JsonResponse
    {
        // Validate — mirrors the Next.js: const { messages, context } = await req.json()
        // messages: array of {role, content}; context: optional string
        $validated = $request->validate([
            'messages'           => 'required|array|min:1',
            'messages.*.role'    => 'required|in:user,assistant',
            'messages.*.content' => 'required|string|max:4000',
            'context'            => 'nullable|string|max:2000',
        ]);

        $apiKey = config('services.openrouter.key');
        $model  = config('services.openrouter.model', 'openai/gpt-oss-20b:free');
        $appUrl = config('app.url', 'http://localhost:8000');

        // Mirror: if (!apiKey) return 500 JSON — but we do it before StreamedResponse
        // so the HTTP status code is actually 500 (not 200 with an error SSE event).
        if (! $apiKey) {
            return response()->json(['error' => 'OPENROUTER_API_KEY not configured'], 500);
        }

        // Build full message list: system + conversation history
        $context  = $validated['context'] ?? null;
        $messages = array_merge(
            [['role' => 'system', 'content' => $this->buildSystemPrompt($context)]],
            collect($validated['messages'])
                ->map(fn ($m) => ['role' => $m['role'], 'content' => $m['content']])
                ->values()
                ->toArray()
        );

        $messageCount = count($validated['messages']);

        return new StreamedResponse(function () use ($messages, $messageCount, $apiKey, $model, $appUrl) {

            Log::info("[chat] → OpenRouter model={$model} messages={$messageCount}");

            // ── 30 s connect timeout (mirrors AbortController 30_000) ─────────
            // curl_setopt is applied via withOptions(['timeout' => N])
            // 'connect_timeout' covers the initial TCP+TLS handshake.
            // 'timeout' is the total time allowed — we set it large here because
            // the *stream* timeout is handled manually below.
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL            => "{$this->baseUrl}/chat/completions",
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    "Authorization: Bearer {$apiKey}",
                    "HTTP-Referer: {$appUrl}",
                    'X-Title: SmartCatalog Web 4.0 Demo',
                ],
                CURLOPT_POSTFIELDS     => json_encode([
                    'model'       => $model,
                    'messages'    => $messages,
                    'max_tokens'  => 800,
                    'temperature' => 0.7,
                    'stream'      => true,
                ]),
                CURLOPT_CONNECTTIMEOUT => 30,    // 30 s connect timeout
                CURLOPT_TIMEOUT        => 90,    // 90 s total (stream timeout handled below)
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_FOLLOWLOCATION => true,
                // CURLOPT_HTTPVERSION    => CURL_HTTP_VERSION_1_1,
            ]);

            // ── Stream-timeout state (mirrors 30 s streamTimeout in Next.js) ──
            $streamStarted  = false;
            $lastChunkAt    = time();
            $streamTimeout  = 30; // seconds — matches Next.js setTimeout 30_000
            $chunkCount     = 0;
            $statusCode     = 0;
            $headersDone    = false;
            $responseBuffer = '';   // only used when !$headersDone (header phase)
            $upstreamError  = null; // set when upstream responds non-200

            // ── Line buffer for SSE parsing ───────────────────────────────────
            $lineBuffer = '';

            // CURLOPT_WRITEFUNCTION is called for every chunk of the response body.
            curl_setopt($curl, CURLOPT_WRITEFUNCTION, function ($ch, $chunk) use (
                &$streamStarted, &$lastChunkAt, &$chunkCount,
                &$lineBuffer, &$upstreamError, &$statusCode
            ) {
                $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                // If upstream returned an error status, buffer the body for JSON parsing
                if ($statusCode >= 400) {
                    $upstreamError .= $chunk;
                    return strlen($chunk);
                }

                if (! $streamStarted) {
                    Log::info('[chat] first chunk received');
                    $streamStarted = true;
                }

                $chunkCount++;
                $lastChunkAt = time();

                // Append chunk to line buffer and process complete lines
                $lineBuffer .= $chunk;

                while (($pos = strpos($lineBuffer, "\n")) !== false) {
                    $line       = substr($lineBuffer, 0, $pos);
                    $lineBuffer = substr($lineBuffer, $pos + 1);
                    $line       = rtrim($line, "\r");

                    if (! str_starts_with($line, 'data: ')) {
                        continue;
                    }

                    $data = substr($line, 6);

                    if ($data === '[DONE]') {
                        echo "data: [DONE]\n\n";
                        flush();
                        return strlen($chunk); // signal handled — curl loop will finish
                    }

                    $json = json_decode($data, true);
                    if (! $json) {
                        continue;
                    }

                    // choices[0].delta.content — mirror: const text = j.choices?.[0]?.delta?.content
                    $text = $json['choices'][0]['delta']['content'] ?? null;
                    if ($text !== null && $text !== '') {
                        echo 'data: ' . json_encode(['text' => $text]) . "\n\n";
                        flush();
                    }

                    // Token usage (last chunk) — mirror: if (j.usage) { … }
                    if (isset($json['usage'])) {
                        $tokens = ($json['usage']['prompt_tokens'] ?? 0)
                                + ($json['usage']['completion_tokens'] ?? 0);
                        echo 'data: ' . json_encode(['tokens' => $tokens]) . "\n\n";
                        flush();
                    }
                }

                return strlen($chunk);
            });

            // ── CURLOPT_PROGRESSFUNCTION — used to check the stream timeout ──
            // Called periodically by libcurl during the transfer.
            curl_setopt($curl, CURLOPT_NOPROGRESS, false);
            curl_setopt($curl, CURLOPT_PROGRESSFUNCTION, function (
                $ch, $dlTotal, $dlNow, $ulTotal, $ulNow
            ) use (&$lastChunkAt, &$streamStarted, &$chunkCount, $streamTimeout) {
                // Only enforce the stream timeout once data has started arriving
                if ($streamStarted && (time() - $lastChunkAt) > $streamTimeout) {
                    Log::warning("[chat] stream timeout after {$chunkCount} chunks");
                    // Returning non-zero aborts the transfer
                    return 1;
                }
                return 0;
            });

            $execError = null;
            curl_exec($curl);

            $curlErrno = curl_errno($curl);
            $curlError = curl_error($curl);
            curl_close($curl);

            // ── Handle upstream error body ────────────────────────────────────
            // Mirror: if (!upstream.ok) { const err = await upstream.json()… }
            if ($upstreamError !== null) {
                $errBody = json_decode($upstreamError, true);
                $errMsg  = $errBody['error']['message'] ?? "OpenRouter API error (HTTP {$statusCode})";
                Log::error("[chat] upstream error status={$statusCode} msg={$errMsg}");
                echo 'data: ' . json_encode(['error' => $errMsg]) . "\n\n";
                echo "data: [DONE]\n\n";
                flush();
                return;
            }

            // ── Handle curl errors (timeout, network, etc.) ───────────────────
            // Mirror: catch(e) { if (e.name==="AbortError") … }
            if ($curlErrno !== 0) {
                $isTimeout = in_array($curlErrno, [CURLE_OPERATION_TIMEDOUT, CURLE_OPERATION_TIMEOUTED]);
                $msg = $isTimeout
                    ? ($streamStarted ? 'Stream read timeout' : 'OpenRouter request timed out')
                    : $curlError;
                Log::error("[chat] curl error errno={$curlErrno} msg={$msg}");
                echo 'data: ' . json_encode(['error' => $msg]) . "\n\n";
                echo "data: [DONE]\n\n";
                flush();
                return;
            }

            // ── Flush any remaining incomplete line in the buffer ─────────────
            if ($lineBuffer !== '') {
                $line = rtrim($lineBuffer, "\r\n");
                if (str_starts_with($line, 'data: ')) {
                    $data = substr($line, 6);
                    if ($data !== '[DONE]') {
                        $json = json_decode($data, true);
                        $text = $json['choices'][0]['delta']['content'] ?? null;
                        if ($text) {
                            echo 'data: ' . json_encode(['text' => $text]) . "\n\n";
                            flush();
                        }
                    }
                }
            }

            Log::info("[chat] stream done after {$chunkCount} chunks");
            echo "data: [DONE]\n\n";
            flush();

        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no',   // disable nginx buffering
            'Connection'        => 'keep-alive',
        ]);
    }
}
