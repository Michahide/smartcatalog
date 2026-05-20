'use client'

import Link from 'next/link'
import { usePathname } from 'next/navigation'
import { LayoutDashboard, Bot, Search, BarChart3, ShoppingBag, LogIn } from 'lucide-react'

const navItems = [
  { href: '/catalog',   label: 'Dashboard',      icon: LayoutDashboard },
  { href: '/chat',      label: 'AI Chatbot',      icon: Bot, badge: 'AI' },
  { href: '/search',    label: 'Smart Search',    icon: Search },
  { href: '/analytics', label: 'Analytics',       icon: BarChart3 },
]

export default function Sidebar() {
  const pathname = usePathname()

  return (
    <aside className="w-[220px] flex-shrink-0 bg-[#0F172A] border-r border-white/5 flex flex-col">
      {/* Logo */}
      <div className="px-5 py-5 border-b border-white/5">
        <div className="flex items-center gap-2">
          <ShoppingBag className="w-5 h-5 text-violet-400" />
          <span className="font-syne text-[18px] font-extrabold bg-gradient-to-r from-violet-400 to-cyan-400 bg-clip-text text-transparent tracking-tight">
            SmartCatalog
          </span>
        </div>
        <p className="text-[10px] text-slate-500 tracking-[1.5px] uppercase mt-1 font-dm">
          Web 4.0 Demo App
        </p>
      </div>

      {/* Nav */}
      <nav className="flex-1 py-3">
        {navItems.map(({ href, label, icon: Icon, badge }) => {
          const active = pathname.startsWith(href)
          return (
            <Link
              key={href}
              href={href}
              className={`flex items-center gap-2.5 px-5 py-2.5 text-[13px] transition-all border-l-[3px] font-dm ${
                active
                  ? 'text-violet-300 bg-violet-500/10 border-violet-500'
                  : 'text-slate-500 border-transparent hover:text-slate-300 hover:bg-violet-500/5'
              }`}
            >
              <Icon className="w-4 h-4 flex-shrink-0" />
              <span>{label}</span>
              {badge && (
                <span className="ml-auto bg-violet-600 text-white text-[10px] px-1.5 py-0.5 rounded-full font-bold">
                  {badge}
                </span>
              )}
            </Link>
          )
        })}
      </nav>

      {/* User */}
      <div className="p-4 border-t border-white/5">
        <div className="flex items-center gap-2.5 bg-[#1E293B] rounded-lg px-3 py-2">
          <div className="w-7 h-7 rounded-full bg-gradient-to-br from-violet-500 to-cyan-500 flex items-center justify-center text-[11px] font-bold text-white flex-shrink-0">
            M
          </div>
          <div className="flex-1 min-w-0">
            <p className="text-[12px] font-medium text-slate-300 truncate">Michael</p>
            <p className="text-[10px] text-slate-500">Developer</p>
          </div>
          <LogIn className="w-3.5 h-3.5 text-slate-600 flex-shrink-0" />
        </div>
      </div>
    </aside>
  )
}
