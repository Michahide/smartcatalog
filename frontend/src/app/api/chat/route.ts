import { NextRequest } from 'next/server'

// ─────────────────────────────────────────────────────────────────────────────
// OpenRouter Integration
// Docs: https://openrouter.ai/docs
// OpenRouter is OpenAI-compatible: uses /chat/completions with the same
// request/response format, just a different base URL and auth header.
// ─────────────────────────────────────────────────────────────────────────────

const OPENROUTER_BASE_URL = 'https://openrouter.ai/api/v1'

// Model used — free tier available, swap to any model on openrouter.ai/models
// Recommended free models for demo:
//   meta-llama/llama-3.3-8b-instruct:free
//   google/gemma-3-27b-it:free
//   mistralai/mistral-7b-instruct:free
const MODEL = process.env.OPENROUTER_MODEL ?? 'meta-llama/llama-3.3-8b-instruct:free'

const SYSTEM_PROMPT = `Kamu adalah AI asisten untuk SmartCatalog, sebuah aplikasi e-commerce berbasis Web 4.0 yang dibangun dengan Next.js 14, Laravel 11, MySQL, Redis, dan OpenRouter AI API.

Produk katalog:
- iPhone 15 Pro (Electronics) - Rp 18.5jt, rating 4.8 ⭐ [AI PICK]
- MacBook Air M3 (Electronics) - Rp 21.9jt, rating 4.9 ⭐ [AI PICK]
- Sony WH-1000XM5 (Electronics) - Rp 5.2jt, rating 4.7 ⭐
- Logitech MX Master 3 (Electronics) - Rp 1.4jt, rating 4.6 ⭐
- Nike Air Max 2024 (Sports) - Rp 2.3jt, rating 4.5 ⭐ [AI PICK]
- Whey Protein Gold (Sports) - Rp 450rb, rating 4.4 ⭐
- Atomic Habits (Books) - Rp 89rb, rating 4.9 ⭐ [AI PICK]
- Uniqlo HEATTECH Tee (Fashion) - Rp 199rb, rating 4.3 ⭐
- Kopi Flores AAA (Food) - Rp 125rb, rating 4.7 ⭐
- iPad Pro M4 (Electronics) - Rp 16.8jt, rating 4.8 ⭐ [AI PICK]
- Levi's 511 Slim (Fashion) - Rp 699rb, rating 4.4 ⭐
- Yoga Mat Premium (Sports) - Rp 380rb, rating 4.6 ⭐

Tugasmu:
- Bantu user temukan produk yang sesuai kebutuhan
- Berikan rekomendasi personal berdasarkan konteks percakapan
- Jelaskan fitur teknis aplikasi (Next.js App Router, Laravel Sanctum, Redis Queue, OpenRouter AI) jika ditanya
- Berikan insight tentang tren, analitik, dan performa AI recommendation
- Selalu jawab dalam Bahasa Indonesia
- Gunakan **bold** untuk nama produk yang direkomendasikan
- Respons ringkas (2-3 paragraf max) dan actionable`

export async function POST(req: NextRequest) {
  const { messages, context } = await req.json()

  const apiKey = process.env.OPENROUTER_API_KEY
  if (!apiKey) {
    return new Response(
      JSON.stringify({ error: 'OPENROUTER_API_KEY not set in .env.local' }),
      { status: 500, headers: { 'Content-Type': 'application/json' } }
    )
  }

  const systemPrompt = context
    ? `${SYSTEM_PROMPT}\n\nKonteks tambahan: ${context}`
    : SYSTEM_PROMPT

  // OpenRouter uses OpenAI-compatible format:
  // system prompt goes as first message with role "system"
  const openRouterMessages = [
    { role: 'system', content: systemPrompt },
    ...messages,
  ]

  const upstream = await fetch(`${OPENROUTER_BASE_URL}/chat/completions`, {
    method: 'POST',
    headers: {
      'Content-Type':    'application/json',
      'Authorization':   `Bearer ${apiKey}`,
      // Required by OpenRouter to identify your app (shown on their dashboard)
      'HTTP-Referer':    process.env.NEXT_PUBLIC_APP_URL ?? 'http://localhost:3000',
      'X-Title':         'SmartCatalog Web 4.0 Demo',
    },
    body: JSON.stringify({
      model:       MODEL,
      messages:    openRouterMessages,
      max_tokens:  800,
      temperature: 0.7,
      stream:      true,
    }),
  })

  if (!upstream.ok) {
    const err = await upstream.json().catch(() => ({ error: { message: upstream.statusText } }))
    return new Response(
      JSON.stringify({ error: err.error?.message ?? 'OpenRouter API error' }),
      { status: upstream.status, headers: { 'Content-Type': 'application/json' } }
    )
  }

  // OpenRouter SSE format (OpenAI-compatible):
  // data: {"choices":[{"delta":{"content":"hello"}}]}
  // data: [DONE]
  //
  // We transform it to our internal format:
  // data: {"text":"hello"}
  // data: [DONE]
  const encoder = new TextEncoder()
  const reader  = upstream.body!.getReader()
  const decoder = new TextDecoder()

  const stream = new ReadableStream({
    async pull(controller) {
      while (true) {
        const { done, value } = await reader.read()
        if (done) {
          controller.enqueue(encoder.encode('data: [DONE]\n\n'))
          controller.close()
          return
        }

        const chunk = decoder.decode(value, { stream: true })
        const lines = chunk.split('\n').filter(l => l.startsWith('data: '))

        for (const line of lines) {
          const data = line.slice(6).trim()
          if (data === '[DONE]') continue

          try {
            const j = JSON.parse(data)
            const text = j.choices?.[0]?.delta?.content
            if (text) {
              controller.enqueue(
                encoder.encode(`data: ${JSON.stringify({ text })}\n\n`)
              )
            }
            // Relay token usage if present
            if (j.usage) {
              const tokens = (j.usage.prompt_tokens ?? 0) + (j.usage.completion_tokens ?? 0)
              controller.enqueue(
                encoder.encode(`data: ${JSON.stringify({ tokens })}\n\n`)
              )
            }
          } catch {
            // Ignore malformed SSE lines
          }
        }
      }
    },
  })

  return new Response(stream, {
    headers: {
      'Content-Type':  'text/event-stream',
      'Cache-Control': 'no-cache',
      'Connection':    'keep-alive',
    },
  })
}
