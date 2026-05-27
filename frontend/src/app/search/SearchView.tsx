'use client'

import { useState, useEffect, useCallback } from 'react'
import { useSearchParams, useRouter } from 'next/navigation'
import { Search } from 'lucide-react'
import { Product } from '@/types'
import { useProduct, useProducts } from '@/hooks/useProducts'

const MOCK_PRODUCTS: Product[] = [
  { id:1,  name:'iPhone 15 Pro',       slug:'iphone-15-pro',       category:'Electronics', price:18500000, price_formatted:'Rp 18.5jt', rating:4.8, rating_count:1243, emoji:'📱', description:'', stock:15,  tags:['Electronics','Tech'],   is_recommended:true,  created_at:'' },
  { id:2,  name:'MacBook Air M3',       slug:'macbook-air-m3',       category:'Electronics', price:21900000, price_formatted:'Rp 21.9jt', rating:4.9, rating_count:879,  emoji:'💻', description:'', stock:8,   tags:['Electronics','Tech'],   is_recommended:true,  created_at:'' },
  { id:3,  name:'Sony WH-1000XM5',      slug:'sony-wh-1000xm5',      category:'Electronics', price:5200000,  price_formatted:'Rp 5.2jt',  rating:4.7, rating_count:654,  emoji:'🎧', description:'', stock:22,  tags:['Electronics'],          is_recommended:false, created_at:'' },
  { id:4,  name:'Logitech MX Master 3', slug:'logitech-mx-master-3', category:'Electronics', price:1400000,  price_formatted:'Rp 1.4jt',  rating:4.6, rating_count:431,  emoji:'🖱️', description:'', stock:35,  tags:['Electronics'],          is_recommended:false, created_at:'' },
  { id:5,  name:'Nike Air Max 2024',    slug:'nike-air-max-2024',    category:'Sports',      price:2300000,  price_formatted:'Rp 2.3jt',  rating:4.5, rating_count:321,  emoji:'👟', description:'', stock:44,  tags:['Sports','Fashion'],     is_recommended:true,  created_at:'' },
  { id:6,  name:'Whey Protein Gold',    slug:'whey-protein-gold',    category:'Sports',      price:450000,   price_formatted:'Rp 450rb',  rating:4.4, rating_count:567,  emoji:'💪', description:'', stock:88,  tags:['Sports'],               is_recommended:false, created_at:'' },
  { id:7,  name:'Atomic Habits',        slug:'atomic-habits',        category:'Books',       price:89000,    price_formatted:'Rp 89rb',   rating:4.9, rating_count:2341, emoji:'📚', description:'', stock:120, tags:['Books'],                is_recommended:true,  created_at:'' },
  { id:8,  name:'Uniqlo HEATTECH Tee',  slug:'uniqlo-heattech',      category:'Fashion',     price:199000,   price_formatted:'Rp 199rb',  rating:4.3, rating_count:890,  emoji:'👕', description:'', stock:200, tags:['Fashion'],              is_recommended:false, created_at:'' },
  { id:9,  name:'Kopi Flores AAA',      slug:'kopi-flores-aaa',      category:'Food',        price:125000,   price_formatted:'Rp 125rb',  rating:4.7, rating_count:445,  emoji:'☕', description:'', stock:66,  tags:['Food'],                 is_recommended:false, created_at:'' },
  { id:10, name:'iPad Pro M4',          slug:'ipad-pro-m4',          category:'Electronics', price:16800000, price_formatted:'Rp 16.8jt', rating:4.8, rating_count:667,  emoji:'📟', description:'', stock:12,  tags:['Electronics','Tech'],   is_recommended:true,  created_at:'' },
  { id:11, name:"Levi's 511 Slim",      slug:'levis-511',            category:'Fashion',     price:699000,   price_formatted:'Rp 699rb',  rating:4.4, rating_count:334,  emoji:'👖', description:'', stock:77,  tags:['Fashion'],              is_recommended:false, created_at:'' },
  { id:12, name:'Yoga Mat Premium',     slug:'yoga-mat-premium',     category:'Sports',      price:380000,   price_formatted:'Rp 380rb',  rating:4.6, rating_count:278,  emoji:'🧘', description:'', stock:55,  tags:['Sports'],               is_recommended:false, created_at:'' },
]

type Scored = Product & { relevance: number }

const SYNONYM_MAP: Record<string, string> = {
  elektronik:'Electronics', tech:'Electronics', gadget:'Electronics', laptop:'Electronics',
  headphone:'Electronics', earphone:'Electronics', audio:'Electronics', hp:'Electronics',
  fashion:'Fashion', baju:'Fashion', outfit:'Fashion', pakaian:'Fashion', casual:'Fashion', celana:'Fashion', kaos:'Fashion',
  olahraga:'Sports', gym:'Sports', fitness:'Sports', sport:'Sports', lari:'Sports', sepatu:'Sports',
  buku:'Books', book:'Books', baca:'Books', novel:'Books', self:'Books',
  makanan:'Food', minuman:'Food', kopi:'Food', food:'Food',
  protein:'Sports', suplemen:'Sports', whey:'Sports',
}

const NAME_BOOST: Record<string, number[]> = {
  laptop: [2], macbook: [2], iphone: [1], ipad: [10],
  headphone: [3], earphone: [3], sony: [3],
  protein: [6], whey: [6], suplemen: [6],
  atomic: [7], habits: [7],
  nike: [5], sepatu: [5],
}

function scoreProducts(query: string, products?: Product[] | null): Scored[] {
  const q = query.toLowerCase().trim()
  const kws = q.split(/\s+/).filter(k => k.length > 1)

  const productsToScore = products || MOCK_PRODUCTS

  return productsToScore.map(p => {
    let score = 0
    const nl = p.name.toLowerCase()

    kws.forEach(kw => {
      if (nl.includes(kw)) score += 0.5
      if (p.category.toLowerCase().includes(kw)) score += 0.3
      const mappedCat = SYNONYM_MAP[kw]
      if (mappedCat && p.category === mappedCat) score += 0.6
      const boosted = NAME_BOOST[kw]
      if (boosted?.includes(p.id)) score += 0.5
    })

    score += p.rating * 0.04
    if (p.is_recommended) score += 0.08

    return { ...p, relevance: Math.min(score, 1.0) }
  })
    .filter(p => p.relevance > 0.1)
    .sort((a, b) => b.relevance - a.relevance)
    .slice(0, 8)
}

const SUGGESTIONS = [
  'laptop gaming terbaik',
  'suplemen untuk fitness',
  'buku self improvement',
  'outfit casual pria',
  'headphone noise cancel',
  'produk tech terbaru',
]

export default function SearchView() {
  const searchParams = useSearchParams()
  const router = useRouter()
  const [query, setQuery] = useState(searchParams.get('q') ?? '')
  const [results, setResults] = useState<Scored[]>([])
  const [loading, setLoading] = useState(false)
  const { data: products } = useProducts()

  const doSearch = useCallback((q: string) => {
    if (!q.trim()) { setResults([]); return }
    setLoading(true)
    setTimeout(() => { setResults(scoreProducts(q)); setLoading(false) }, 320)
  }, [])

  useEffect(() => {
    const q = searchParams.get('q')
    if (q) { setQuery(q); doSearch(q) }
  }, [searchParams, doSearch])

  function handleInput(val: string) {
    setQuery(val)
    doSearch(val)
  }

  function handleSuggestion(s: string) {
    setQuery(s); doSearch(s)
    router.push(`/search?q=${encodeURIComponent(s)}`)
  }

  return (
    <div className="max-w-2xl">
      <h1 className="font-syne text-[22px] font-extrabold mb-1.5">Semantic Search</h1>
      <p className="text-[13px] text-slate-400 mb-5">
        Cari produk menggunakan bahasa natural — AI memahami maksud Anda, bukan hanya kata kunci.
      </p>

      {/* Search input */}
      <div className="relative mb-4">
        <Search className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" />
        <input
          value={query}
          onChange={e => handleInput(e.target.value)}
          placeholder="Contoh: 'produk untuk olahraga di gym' atau 'hadiah untuk anak muda'"
          className="w-full bg-[#1E293B] border border-white/5 focus:border-cyan-500/50 rounded-xl pl-11 pr-4 py-3 text-[14px] text-slate-200 placeholder-slate-500 outline-none transition-colors"
        />
      </div>

      {/* Suggestion chips */}
      <div className="flex flex-wrap gap-2 mb-5">
        {SUGGESTIONS.map(s => (
          <button
            key={s}
            onClick={() => handleSuggestion(s)}
            className="text-[11px] px-3 py-1.5 rounded-full border border-white/10 text-slate-400 hover:text-cyan-300 hover:border-cyan-500/40 hover:bg-cyan-500/[0.07] transition-all"
          >
            {s}
          </button>
        ))}
      </div>

      {/* Results */}
      {loading && (
        <div className="text-center py-10 text-slate-400 text-[13px]">
          🤖 AI sedang mencari produk yang relevan...
        </div>
      )}

      {!loading && results.length > 0 && (
        <div className="bg-[#1E293B]/80 border border-white/5 rounded-xl overflow-hidden">
          <div className="px-4 py-2.5 border-b border-white/5 text-[11px] text-slate-500">
            <span className="text-slate-300 font-medium">{results.length} hasil</span>
            {' '}untuk &quot;{query}&quot; — diurutkan berdasarkan relevansi AI
          </div>
          {results.map((p, i) => (
            <div
              key={p.id}
              className="flex items-start gap-3 px-4 py-3 border-b border-white/5 last:border-0 hover:bg-white/[0.02] transition-colors cursor-pointer"
            >
              <span className="font-syne text-[11px] font-bold text-slate-500 w-5 pt-0.5 flex-shrink-0">
                #{i + 1}
              </span>
              <span className="text-[20px] flex-shrink-0">{p.emoji}</span>
              <div className="flex-1 min-w-0">
                <p className="text-[13px] font-medium text-slate-200">{p.name}</p>
                <p className="text-[11px] text-slate-500 mb-1.5">
                  {p.category} · {p.price_formatted} · ★ {p.rating}
                </p>
                <div className="flex items-center gap-1.5 text-[11px]">
                  <span className="text-slate-500">Relevansi</span>
                  <div className="w-16 h-1 bg-[#0F172A] rounded-full overflow-hidden">
                    <div
                      className="h-full rounded-full bg-gradient-to-r from-violet-500 to-cyan-500 transition-all duration-500"
                      style={{ width: `${Math.round(p.relevance * 100)}%` }}
                    />
                  </div>
                  <span className="text-slate-300 font-medium">{Math.round(p.relevance * 100)}%</span>
                </div>
              </div>
              {p.is_recommended && (
                <span className="text-[10px] px-2 py-0.5 bg-violet-500/15 text-violet-300 rounded-full flex-shrink-0 self-center border border-violet-500/20">
                  AI PICK
                </span>
              )}
            </div>
          ))}
        </div>
      )}

      {!loading && query && results.length === 0 && (
        <div className="text-center py-10 text-slate-400 text-[13px]">
          Tidak ada produk yang cocok untuk &quot;{query}&quot; — coba kata kunci lain.
        </div>
      )}

      {!loading && !query && (
        <div className="flex flex-col items-center py-14 text-slate-500 gap-2">
          <span className="text-4xl">🔍</span>
          <p className="text-[13px]">Ketik untuk mulai mencari produk dengan AI</p>
        </div>
      )}
    </div>
  )
}
