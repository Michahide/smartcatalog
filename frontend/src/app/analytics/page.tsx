'use client'

import { useAnalytics } from '@/hooks/useAnalytics'

const MOCK_ACTIVITY = [
  { icon: '🤖', text: 'AI merekomendasikan 3 produk ke User #4821', time: '2m ago', color: 'bg-violet-500/15' },
  { icon: '🛒', text: 'Pembelian: iPhone 15 Pro – Rp 18.5jt', time: '5m ago', color: 'bg-emerald-500/15' },
  { icon: '🔍', text: 'Semantic search: "earphone noise cancel"', time: '8m ago', color: 'bg-cyan-500/15' },
  { icon: '💬', text: 'Chatbot session dimulai oleh User #3309', time: '12m ago', color: 'bg-amber-500/15' },
  { icon: '⚠️', text: 'Stok Bose QC45 tersisa 2 unit', time: '18m ago', color: 'bg-red-500/15' },
]

const SALES = [
  { cat: 'Elektronik', pct: 82, color: 'bg-violet-500' },
  { cat: 'Fashion',    pct: 64, color: 'bg-cyan-500' },
  { cat: 'Sports',     pct: 58, color: 'bg-emerald-500' },
  { cat: 'Food',       pct: 45, color: 'bg-amber-500' },
  { cat: 'Books',      pct: 31, color: 'bg-pink-500' },
]

const AI_KPI = [
  { label: 'Click-Through Rate', value: '34%',   color: 'text-violet-300', bg: 'bg-violet-500/10' },
  { label: 'Revenue Lift',       value: '7.2x',  color: 'text-cyan-300',   bg: 'bg-cyan-500/10' },
  { label: 'Accuracy',           value: '92%',   color: 'text-emerald-300',bg: 'bg-emerald-500/10' },
  { label: 'Avg Latency',        value: '1.8s',  color: 'text-amber-300',  bg: 'bg-amber-500/10' },
]

export default function AnalyticsPage() {
  const { data } = useAnalytics()
  const activity = data?.activity_log ?? MOCK_ACTIVITY

  return (
    <div className="grid grid-cols-2 gap-4">

      {/* Sales by category */}
      <div className="bg-[#1E293B]/80 border border-white/5 rounded-xl p-5">
        <h2 className="font-syne text-[13px] font-bold mb-4">Penjualan per Kategori</h2>
        <div className="flex flex-col gap-3">
          {SALES.map(s => (
            <div key={s.cat} className="flex items-center gap-3">
              <span className="text-[11px] text-slate-500 w-20 text-right flex-shrink-0">{s.cat}</span>
              <div className="flex-1 h-2 bg-[#0F172A] rounded-full overflow-hidden">
                <div
                  className={`h-full rounded-full transition-all duration-700 ${s.color}`}
                  style={{ width: `${s.pct}%` }}
                />
              </div>
              <span className="text-[11px] text-slate-300 font-medium w-9 text-right">{s.pct}%</span>
            </div>
          ))}
        </div>
      </div>

      {/* Traffic source donut */}
      <div className="bg-[#1E293B]/80 border border-white/5 rounded-xl p-5">
        <h2 className="font-syne text-[13px] font-bold mb-4">Sumber Traffic</h2>
        <div className="flex items-center gap-6">
          <svg width="110" height="110" viewBox="0 0 110 110">
            <circle cx="55" cy="55" r="40" fill="none" stroke="#0F172A" strokeWidth="20"/>
            <circle cx="55" cy="55" r="40" fill="none" stroke="#7C3AED" strokeWidth="20"
              strokeDasharray="100 151.8" strokeDashoffset="38" transform="rotate(-90 55 55)"/>
            <circle cx="55" cy="55" r="40" fill="none" stroke="#06B6D4" strokeWidth="20"
              strokeDasharray="60 151.8" strokeDashoffset="-62" transform="rotate(-90 55 55)"/>
            <circle cx="55" cy="55" r="40" fill="none" stroke="#10B981" strokeWidth="20"
              strokeDasharray="38 151.8" strokeDashoffset="-122" transform="rotate(-90 55 55)"/>
            <circle cx="55" cy="55" r="40" fill="none" stroke="#F59E0B" strokeWidth="20"
              strokeDasharray="15 151.8" strokeDashoffset="-160" transform="rotate(-90 55 55)"/>
            <text x="55" y="51" textAnchor="middle" fill="#F8FAFC" fontSize="14" fontWeight="700" fontFamily="Syne, sans-serif">39%</text>
            <text x="55" y="63" textAnchor="middle" fill="#64748B" fontSize="9">Organic</text>
          </svg>
          <div className="flex flex-col gap-2">
            {[
              { label: 'Organic 39%',  color: 'bg-violet-500' },
              { label: 'Direct 24%',   color: 'bg-cyan-500' },
              { label: 'Social 15%',   color: 'bg-emerald-500' },
              { label: 'Other 22%',    color: 'bg-amber-500' },
            ].map(item => (
              <div key={item.label} className="flex items-center gap-2 text-[12px] text-slate-300">
                <div className={`w-2.5 h-2.5 rounded-full ${item.color}`} />
                {item.label}
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* AI Performance */}
      <div className="bg-[#1E293B]/80 border border-white/5 rounded-xl p-5">
        <h2 className="font-syne text-[13px] font-bold mb-4">Performa AI Recommendation</h2>
        <div className="grid grid-cols-2 gap-3">
          {AI_KPI.map(kpi => (
            <div key={kpi.label} className={`${kpi.bg} rounded-xl p-3.5 text-center`}>
              <p className={`font-syne text-[22px] font-extrabold ${kpi.color}`}>{kpi.value}</p>
              <p className="text-[10px] text-slate-500 mt-1">{kpi.label}</p>
            </div>
          ))}
        </div>
      </div>

      {/* Activity log */}
      <div className="bg-[#1E293B]/80 border border-white/5 rounded-xl p-5">
        <h2 className="font-syne text-[13px] font-bold mb-4">Activity Log</h2>
        <div className="flex flex-col">
          {activity.map((act, i) => (
            <div key={i} className="flex items-center gap-3 py-2.5 border-b border-white/5 last:border-0">
              <div className={`w-7 h-7 rounded-lg ${act.color} flex items-center justify-center text-[13px] flex-shrink-0`}>
                {act.icon}
              </div>
              <p className="flex-1 text-[12px] text-slate-300">{act.text}</p>
              <span className="text-[11px] text-slate-500 flex-shrink-0">{act.time}</span>
            </div>
          ))}
        </div>
      </div>

    </div>
  )
}
