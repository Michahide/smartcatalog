'use client'

import { useState } from 'react'
import { usePathname, useRouter } from 'next/navigation'
import { Bell, User, Search } from 'lucide-react'
import AuthModal from '@/components/ui/AuthModal'

const titles: Record<string, string> = {
  '/catalog':   'Dashboard',
  '/chat':      'AI Chatbot',
  '/search':    'Semantic Search',
  '/analytics': 'Analytics',
}

export default function Topbar() {
  const pathname = usePathname()
  const router = useRouter()
  const [showAuth, setShowAuth] = useState(false)
  const [q, setQ] = useState('')

  const title = Object.entries(titles).find(([k]) => pathname.startsWith(k))?.[1] ?? 'SmartCatalog'

  function handleSearch(e: React.FormEvent) {
    e.preventDefault()
    if (q.trim()) {
      router.push(`/search?q=${encodeURIComponent(q.trim())}`)
      setQ('')
    }
  }

  return (
    <>
      <header className="h-[52px] flex items-center px-6 gap-3 border-b border-white/5 bg-[#0F172A] flex-shrink-0">
        <h1 className="font-syne text-[15px] font-bold">{title}</h1>
        <span className="text-[11px] px-2.5 py-0.5 rounded-full font-semibold bg-emerald-500/15 text-emerald-400">
          ● Live
        </span>
        <div className="flex-1" />
        <form onSubmit={handleSearch} className="flex items-center gap-2 bg-[#1E293B] border border-white/5 rounded-lg px-3 py-1.5 w-[220px]">
          <Search className="w-3.5 h-3.5 text-slate-500 flex-shrink-0" />
          <input
            value={q}
            onChange={e => setQ(e.target.value)}
            type="text"
            placeholder="Quick search..."
            className="bg-transparent text-[13px] text-slate-200 placeholder-slate-500 outline-none w-full font-dm"
          />
        </form>
        <button className="w-8 h-8 rounded-lg border border-white/5 bg-transparent flex items-center justify-center text-slate-500 hover:text-slate-200 hover:bg-[#1E293B] transition-colors">
          <Bell className="w-4 h-4" />
        </button>
        <button
          onClick={() => setShowAuth(true)}
          className="w-8 h-8 rounded-lg border border-white/5 bg-transparent flex items-center justify-center text-slate-500 hover:text-slate-200 hover:bg-[#1E293B] transition-colors"
        >
          <User className="w-4 h-4" />
        </button>
      </header>

      {showAuth && <AuthModal onClose={() => setShowAuth(false)} />}
    </>
  )
}
