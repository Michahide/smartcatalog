'use client'

import ChatWindow from '@/components/chat/ChatWindow'

export default function ChatPage() {
  return (
    <div className="grid grid-cols-[1fr_260px] gap-4" style={{ height: 'calc(100vh - 148px)' }}>
      {/* Main chat */}
      <div className="bg-[#1E293B]/80 border border-white/5 rounded-xl flex flex-col overflow-hidden">
        <div className="flex items-center gap-2 px-4 py-3.5 border-b border-white/5 flex-shrink-0">
          <div className="w-[7px] h-[7px] rounded-full bg-violet-500" />
          <h2 className="font-syne text-[13px] font-bold">AI Chatbot — Full Mode</h2>
          <span className="ml-auto text-[11px] text-slate-500">Context-aware · Streaming</span>
        </div>
        <ChatWindow />
      </div>

      {/* Sidebar panels */}
      <div className="flex flex-col gap-3">
        {/* User context */}
        <div className="bg-[#1E293B]/80 border border-white/5 rounded-xl overflow-hidden">
          <div className="flex items-center gap-2 px-4 py-3 border-b border-white/5">
            <div className="w-[7px] h-[7px] rounded-full bg-emerald-500" />
            <h3 className="font-syne text-[12px] font-bold">Konteks User</h3>
          </div>
          <div className="p-3.5 flex flex-col gap-2">
            <p className="text-[11px] text-slate-500">Preferensi</p>
            <div className="flex flex-wrap gap-1.5">
              {['Elektronik', 'Gaming', 'Tech'].map(tag => (
                <span key={tag} className="text-[11px] px-2 py-0.5 rounded-full bg-violet-500/15 text-violet-300">
                  {tag}
                </span>
              ))}
            </div>
            <p className="text-[11px] text-slate-500 mt-1">Riwayat Klik</p>
            <p className="text-[12px] text-slate-300">12 produk terakhir dilihat</p>
          </div>
        </div>

        {/* API Status */}
        <div className="bg-[#1E293B]/80 border border-white/5 rounded-xl overflow-hidden">
          <div className="flex items-center gap-2 px-4 py-3 border-b border-white/5">
            <div className="w-[7px] h-[7px] rounded-full bg-amber-500" />
            <h3 className="font-syne text-[12px] font-bold">API Status</h3>
          </div>
          <div className="p-3.5 space-y-2">
            {[
              { label: 'Model',   value: 'llama-3.3-8b' },
              { label: 'Stream',  value: 'SSE enabled' },
              { label: 'Backend', value: 'Laravel 11' },
              { label: 'Cache',   value: 'Redis 7' },
            ].map(row => (
              <div key={row.label} className="flex justify-between text-[12px]">
                <span className="text-slate-500">{row.label}</span>
                <span className="text-slate-300">{row.value}</span>
              </div>
            ))}
            <div className="flex justify-between text-[12px]">
              <span className="text-slate-500">Status</span>
              <span className="text-emerald-400">● Connected</span>
            </div>
          </div>
        </div>

        {/* Tech stack used */}
        <div className="bg-[#1E293B]/80 border border-white/5 rounded-xl overflow-hidden">
          <div className="flex items-center gap-2 px-4 py-3 border-b border-white/5">
            <div className="w-[7px] h-[7px] rounded-full bg-cyan-500" />
            <h3 className="font-syne text-[12px] font-bold">Tech Stack</h3>
          </div>
          <div className="p-3.5 space-y-1.5">
            {[
              { layer: 'Frontend', tech: 'Next.js 14 App Router', color: 'text-cyan-400' },
              { layer: 'Backend',  tech: 'Laravel 11 + Sanctum',   color: 'text-violet-400' },
              { layer: 'AI',       tech: 'OpenRouter AI API',    color: 'text-emerald-400' },
              { layer: 'DB',       tech: 'MySQL + Redis',           color: 'text-amber-400' },
            ].map(row => (
              <div key={row.layer} className="text-[11px]">
                <span className="text-slate-500">{row.layer}: </span>
                <span className={row.color}>{row.tech}</span>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  )
}
