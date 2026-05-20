import { TrendingUp, TrendingDown } from 'lucide-react'

interface StatCardProps {
  label: string
  value: string | number
  delta: string
  positive?: boolean
  accentColor: string
}

function StatCard({ label, value, delta, positive = true, accentColor }: StatCardProps) {
  return (
    <div className="relative bg-[#1E293B]/80 border border-white/5 rounded-xl p-4 overflow-hidden">
      <div className={`absolute top-0 left-0 right-0 h-[2px] ${accentColor}`} />
      <p className="text-[11px] text-slate-500 uppercase tracking-[0.8px] mb-1.5">{label}</p>
      <p className="font-syne text-[24px] font-bold leading-none">{value}</p>
      <p className={`text-[11px] mt-1.5 flex items-center gap-1 ${positive ? 'text-emerald-400' : 'text-red-400'}`}>
        {positive ? <TrendingUp className="w-3 h-3" /> : <TrendingDown className="w-3 h-3" />}
        {delta}
      </p>
    </div>
  )
}

interface Props {
  data?: {
    total_products: number
    ai_recommendations: number
    active_sessions: number
    revenue_formatted: string
  }
}

export default function StatsRow({ data }: Props) {
  const stats = [
    { label: 'Total Products',      value: data?.total_products ?? 248,   delta: '12 this week',   positive: true,  accentColor: 'bg-violet-500' },
    { label: 'AI Recommendations',  value: data?.ai_recommendations ?? '1.2K', delta: '34% CTR', positive: true,  accentColor: 'bg-cyan-500' },
    { label: 'Active Sessions',      value: data?.active_sessions ?? 87,   delta: '8 online now',   positive: true,  accentColor: 'bg-emerald-500' },
    { label: 'Revenue Today',        value: data?.revenue_formatted ?? 'Rp 4.2M', delta: '3% vs yesterday', positive: false, accentColor: 'bg-amber-500' },
  ]

  return (
    <div className="grid grid-cols-4 gap-3 mb-6">
      {stats.map(s => <StatCard key={s.label} {...s} />)}
    </div>
  )
}
