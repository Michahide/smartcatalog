'use client'

import { useState } from 'react'
import { X, Lock } from 'lucide-react'
import { useAuth } from '@/hooks/useAuth'

export default function AuthModal({ onClose }: { onClose: () => void }) {
  const { login, logout, user, isAuthenticated } = useAuth()
  const [email, setEmail] = useState('michael@dipa.co.id')
  const [password, setPassword] = useState('password')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')

  async function handleLogin(e: React.FormEvent) {
    e.preventDefault()
    setLoading(true)
    setError('')
    try {
      await login(email, password)
      onClose()
    } catch (err: unknown) {
      const msg = (err as { response?: { data?: { message?: string } } })?.response?.data?.message
      setError(msg ?? 'Login failed. Check your credentials.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div
      className="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50"
      onClick={e => { if (e.target === e.currentTarget) onClose() }}
    >
      <div className="bg-[#0F172A] border border-white/10 rounded-xl w-[360px] p-8 relative">
        <button onClick={onClose} className="absolute top-4 right-4 text-slate-500 hover:text-slate-300">
          <X className="w-4 h-4" />
        </button>

        <div className="flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/20 rounded-full px-3 py-1 w-fit mb-5">
          <Lock className="w-3 h-3 text-emerald-400" />
          <span className="text-[11px] text-emerald-400 font-semibold">JWT Authentication</span>
        </div>

        {isAuthenticated ? (
          <div>
            <h2 className="font-syne text-xl font-extrabold mb-1">Welcome, {user?.name}</h2>
            <p className="text-[13px] text-slate-400 mb-6">{user?.email}</p>
            <button
              onClick={() => { logout(); onClose() }}
              className="w-full bg-red-600 hover:bg-red-700 rounded-lg py-2.5 text-[14px] font-semibold font-syne transition-colors"
            >
              Logout
            </button>
          </div>
        ) : (
          <>
            <h2 className="font-syne text-xl font-extrabold mb-1">Masuk ke SmartCatalog</h2>
            <p className="text-[13px] text-slate-400 mb-6">Demo auth dengan Laravel Sanctum + Bearer Token</p>

            {error && (
              <div className="mb-4 text-[12px] text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2">
                {error}
              </div>
            )}

            <form onSubmit={handleLogin} className="space-y-3">
              <div>
                <label className="block text-[12px] text-slate-400 mb-1.5">Email</label>
                <input
                  type="email" value={email} onChange={e => setEmail(e.target.value)} required
                  className="w-full bg-[#1E293B] border border-white/10 rounded-lg px-3 py-2 text-[13px] text-slate-200 outline-none focus:border-violet-500/60 transition-colors font-dm"
                />
              </div>
              <div>
                <label className="block text-[12px] text-slate-400 mb-1.5">Password</label>
                <input
                  type="password" value={password} onChange={e => setPassword(e.target.value)} required
                  className="w-full bg-[#1E293B] border border-white/10 rounded-lg px-3 py-2 text-[13px] text-slate-200 outline-none focus:border-violet-500/60 transition-colors font-dm"
                />
              </div>
              <button
                type="submit" disabled={loading}
                className="w-full mt-1 bg-violet-600 hover:bg-violet-700 disabled:opacity-50 rounded-lg py-2.5 text-[14px] font-semibold font-syne transition-colors"
              >
                {loading ? 'Logging in...' : 'Login →'}
              </button>
            </form>

            <div className="flex items-center gap-2 my-4 text-slate-600 text-[12px]">
              <div className="flex-1 h-px bg-white/5" />atau<div className="flex-1 h-px bg-white/5" />
            </div>
            <button className="w-full border border-white/10 rounded-lg py-2.5 text-[13px] text-slate-300 hover:bg-white/5 transition-colors mb-2 font-dm">
              🔑 &nbsp;Login dengan Keycloak SSO
            </button>
            <button className="w-full border border-white/10 rounded-lg py-2.5 text-[13px] text-slate-300 hover:bg-white/5 transition-colors font-dm">
              🌐 &nbsp;Login dengan Google OAuth
            </button>
          </>
        )}
      </div>
    </div>
  )
}
