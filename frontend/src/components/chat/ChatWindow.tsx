'use client'

import { useState, useRef, useEffect, useCallback } from 'react'
import { Send, Loader2 } from 'lucide-react'
import { ChatMessage } from '@/types'

interface Props {
  compact?: boolean
  systemContext?: string
}

const QUICK_PROMPTS = [
  'Rekomendasikan produk untuk saya',
  'Produk terlaris minggu ini?',
  'Tren produk yang sedang naik?',
]

const QUICK_PROMPTS_FULL = [
  'Rekomendasikan 3 produk elektronik terbaik',
  'Analisis performa penjualan bulan ini',
  'Produk apa yang paling banyak dicari?',
  'Jelaskan bagaimana sistem rekomendasi AI bekerja',
]

export default function ChatWindow({ compact = false, systemContext }: Props) {
  const [messages, setMessages] = useState<ChatMessage[]>([
    {
      role: 'assistant',
      content: compact
        ? 'Halo! Tanyakan tentang produk, rekomendasi, atau analitik. 🛍️'
        : 'Selamat datang di **SmartCatalog AI**!\n\nSaya dapat membantu Anda dengan:\n• Rekomendasi produk personal\n• Analisis tren & penjualan\n• Pencarian semantik produk\n• Insight dashboard & laporan',
    },
  ])
  const [input, setInput] = useState('')
  const [loading, setLoading] = useState(false)
  const [tokenCount, setTokenCount] = useState(0)
  const [latency, setLatency] = useState<string | null>(null)
  const bottomRef = useRef<HTMLDivElement>(null)
  const inputRef = useRef<HTMLTextAreaElement>(null)

  useEffect(() => {
    bottomRef.current?.scrollIntoView({ behavior: 'smooth' })
  }, [messages, loading])

  const send = useCallback(async (text: string) => {
    if (!text.trim() || loading) return
    const userMsg: ChatMessage = { role: 'user', content: text }
    setMessages(prev => [...prev, userMsg])
    setInput('')
    setLoading(true)

    const t0 = Date.now()
    let assistantContent = ''

    try {
      const history = [...messages, userMsg]
        .filter(m => m.role === 'user' || m.role === 'assistant')
        .slice(-12)

      const res = await fetch('/api/chat', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ messages: history, context: systemContext }),
      })

      if (!res.ok) throw new Error(`HTTP ${res.status}`)
      if (!res.body) throw new Error('No response body')

      const reader = res.body.getReader()
      const decoder = new TextDecoder()
      setLoading(false)

      setMessages(prev => [...prev, { role: 'assistant', content: '' }])

      while (true) {
        const { done, value } = await reader.read()
        if (done) break
        const chunk = decoder.decode(value)
        const lines = chunk.split('\n').filter(l => l.startsWith('data: '))

        for (const line of lines) {
          const data = line.slice(6)
          if (data === '[DONE]') break
          try {
            const j = JSON.parse(data)
            if (j.text) {
              assistantContent += j.text
              setMessages(prev => {
                const updated = [...prev]
                updated[updated.length - 1] = { role: 'assistant', content: assistantContent }
                return updated
              })
            }
            if (j.tokens) setTokenCount(prev => prev + j.tokens)
          } catch { /* ignore parse errors */ }
        }
      }

      setLatency(((Date.now() - t0) / 1000).toFixed(1) + 's')
    } catch (err) {
      setLoading(false)
      setMessages(prev => [...prev, {
        role: 'assistant',
        content: `**Error:** ${(err as Error).message}. Pastikan backend Laravel sudah berjalan.`,
      }])
    }

    inputRef.current?.focus()
  }, [loading, messages, systemContext])

  function handleKey(e: React.KeyboardEvent) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault()
      send(input)
    }
  }

  function formatContent(text: string) {
    return text
      .replace(/\*\*(.*?)\*\*/g, '<strong class="text-violet-300">$1</strong>')
      .replace(/\*(.*?)\*/g, '<em>$1</em>')
      .replace(/•/g, '·')
      .replace(/\n/g, '<br />')
  }

  const prompts = compact ? QUICK_PROMPTS : QUICK_PROMPTS_FULL

  return (
    <div className="flex flex-col h-full">
      {/* Messages */}
      <div className="flex-1 overflow-y-auto p-4 flex flex-col gap-2.5 min-h-0">
        {messages.map((msg, i) => (
          <div
            key={i}
            className={`max-w-[85%] text-[13px] leading-relaxed ${
              msg.role === 'user'
                ? 'self-end bg-violet-600 text-white px-3 py-2 rounded-xl rounded-br-sm'
                : 'self-start bg-[#1E293B] text-slate-300 px-3 py-2 rounded-xl rounded-bl-sm border border-white/5'
            }`}
            dangerouslySetInnerHTML={{ __html: formatContent(msg.content) }}
          />
        ))}

        {loading && (
          <div className="self-start bg-[#1E293B] border border-white/5 px-3 py-2 rounded-xl rounded-bl-sm">
            <div className="flex gap-1 items-center py-0.5">
              {[0, 1, 2].map(i => (
                <span
                  key={i}
                  className="w-1.5 h-1.5 rounded-full bg-slate-500 animate-bounce"
                  style={{ animationDelay: `${i * 0.15}s` }}
                />
              ))}
            </div>
          </div>
        )}
        <div ref={bottomRef} />
      </div>

      {/* Quick prompts */}
      <div className="px-3 pb-2 flex flex-wrap gap-1.5">
        {prompts.map(p => (
          <button
            key={p}
            onClick={() => send(p)}
            className="text-[11px] px-2.5 py-1 rounded-full border border-white/10 text-slate-400 hover:text-violet-300 hover:border-violet-500/40 hover:bg-violet-500/7 transition-all font-dm"
          >
            {p}
          </button>
        ))}
      </div>

      {/* Status bar (full mode) */}
      {!compact && (
        <div className="px-3 pb-1 flex gap-3 text-[10px] text-slate-500">
          <span>Tokens: <span className="text-slate-400">{tokenCount.toLocaleString()}</span></span>
          {latency && <span>Latency: <span className="text-slate-400">{latency}</span></span>}
          <span className="ml-auto text-emerald-500">● Connected</span>
        </div>
      )}

      {/* Input */}
      <div className="p-3 border-t border-white/5 flex gap-2">
        <textarea
          ref={inputRef}
          value={input}
          onChange={e => setInput(e.target.value)}
          onKeyDown={handleKey}
          placeholder="Ketik pesan..."
          rows={1}
          className="flex-1 bg-[#1E293B] border border-white/5 focus:border-violet-500/50 rounded-lg px-3 py-2 text-[13px] text-slate-200 placeholder-slate-500 outline-none resize-none font-dm transition-colors"
          style={{ height: '36px', lineHeight: '20px' }}
        />
        <button
          onClick={() => send(input)}
          disabled={loading || !input.trim()}
          className="w-9 h-9 bg-violet-600 hover:bg-violet-700 disabled:opacity-40 rounded-lg flex items-center justify-center text-white transition-colors flex-shrink-0"
        >
          {loading ? <Loader2 className="w-4 h-4 animate-spin" /> : <Send className="w-4 h-4" />}
        </button>
      </div>
    </div>
  )
}
