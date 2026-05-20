'use client'

import { useState } from 'react'
import { useProducts } from '@/hooks/useProducts'
import { useAnalytics } from '@/hooks/useAnalytics'
import { Product } from '@/types'
import ProductCard from '@/components/catalog/ProductCard'
import StatsRow from '@/components/catalog/StatsRow'
import ChatWindow from '@/components/chat/ChatWindow'

const CATEGORIES = ['all', 'Electronics', 'Fashion', 'Food', 'Books', 'Sports']

// Mock products as fallback while backend is being set up
const MOCK_PRODUCTS: Product[] = [
  { id:1, name:'iPhone 15 Pro', slug:'iphone-15-pro', category:'Electronics', price:18500000, price_formatted:'Rp 18.5jt', rating:4.8, rating_count:1243, emoji:'📱', description:'', stock:15, tags:['Electronics','Tech'], is_recommended:true, created_at:'' },
  { id:2, name:'MacBook Air M3', slug:'macbook-air-m3', category:'Electronics', price:21900000, price_formatted:'Rp 21.9jt', rating:4.9, rating_count:879, emoji:'💻', description:'', stock:8, tags:['Electronics','Tech'], is_recommended:true, created_at:'' },
  { id:3, name:'Sony WH-1000XM5', slug:'sony-wh-1000xm5', category:'Electronics', price:5200000, price_formatted:'Rp 5.2jt', rating:4.7, rating_count:654, emoji:'🎧', description:'', stock:22, tags:['Electronics'], is_recommended:false, created_at:'' },
  { id:4, name:'Logitech MX Master 3', slug:'logitech-mx-master-3', category:'Electronics', price:1400000, price_formatted:'Rp 1.4jt', rating:4.6, rating_count:431, emoji:'🖱️', description:'', stock:35, tags:['Electronics'], is_recommended:false, created_at:'' },
  { id:5, name:'Nike Air Max 2024', slug:'nike-air-max-2024', category:'Sports', price:2300000, price_formatted:'Rp 2.3jt', rating:4.5, rating_count:321, emoji:'👟', description:'', stock:44, tags:['Sports','Fashion'], is_recommended:true, created_at:'' },
  { id:6, name:'Whey Protein Gold', slug:'whey-protein-gold', category:'Sports', price:450000, price_formatted:'Rp 450rb', rating:4.4, rating_count:567, emoji:'💪', description:'', stock:88, tags:['Sports'], is_recommended:false, created_at:'' },
  { id:7, name:'Atomic Habits', slug:'atomic-habits', category:'Books', price:89000, price_formatted:'Rp 89rb', rating:4.9, rating_count:2341, emoji:'📚', description:'', stock:120, tags:['Books'], is_recommended:true, created_at:'' },
  { id:8, name:'Uniqlo HEATTECH Tee', slug:'uniqlo-heattech', category:'Fashion', price:199000, price_formatted:'Rp 199rb', rating:4.3, rating_count:890, emoji:'👕', description:'', stock:200, tags:['Fashion'], is_recommended:false, created_at:'' },
  { id:9, name:'Kopi Flores AAA', slug:'kopi-flores-aaa', category:'Food', price:125000, price_formatted:'Rp 125rb', rating:4.7, rating_count:445, emoji:'☕', description:'', stock:66, tags:['Food'], is_recommended:false, created_at:'' },
  { id:10, name:'iPad Pro M4', slug:'ipad-pro-m4', category:'Electronics', price:16800000, price_formatted:'Rp 16.8jt', rating:4.8, rating_count:667, emoji:'📟', description:'', stock:12, tags:['Electronics','Tech'], is_recommended:true, created_at:'' },
  { id:11, name:"Levi's 511 Slim", slug:'levis-511', category:'Fashion', price:699000, price_formatted:'Rp 699rb', rating:4.4, rating_count:334, emoji:'👖', description:'', stock:77, tags:['Fashion'], is_recommended:false, created_at:'' },
  { id:12, name:'Yoga Mat Premium', slug:'yoga-mat-premium', category:'Sports', price:380000, price_formatted:'Rp 380rb', rating:4.6, rating_count:278, emoji:'🧘', description:'', stock:55, tags:['Sports'], is_recommended:false, created_at:'' },
]

export default function CatalogPage() {
  const [activeCategory, setActiveCategory] = useState('all')
  const { data: productsData, isError } = useProducts(activeCategory)
  const { data: analyticsData } = useAnalytics()

  // Use mock data as fallback
  const products = isError || !productsData
    ? (activeCategory === 'all' ? MOCK_PRODUCTS : MOCK_PRODUCTS.filter(p => p.tags.includes(activeCategory)))
    : productsData.data

  return (
    <div>
      <StatsRow data={analyticsData} />

      <div className="grid grid-cols-[1fr_340px] gap-4">
        {/* Catalog panel */}
        <div className="bg-[#1E293B]/80 border border-white/5 rounded-xl overflow-hidden">
          <div className="flex items-center gap-2 px-4 py-3.5 border-b border-white/5">
            <div className="w-[7px] h-[7px] rounded-full bg-violet-500" />
            <h2 className="font-syne text-[13px] font-bold">Product Catalog</h2>
            <span className="ml-auto text-[11px] text-slate-500">{products.length} produk</span>
          </div>

          {/* Filters */}
          <div className="flex gap-2 px-4 py-3 border-b border-white/5 flex-wrap">
            {CATEGORIES.map(cat => (
              <button
                key={cat}
                onClick={() => setActiveCategory(cat)}
                className={`px-3 py-1 rounded-full text-[11px] border transition-all font-dm ${
                  activeCategory === cat
                    ? 'bg-violet-500/15 text-violet-300 border-violet-500/40'
                    : 'border-white/5 text-slate-500 hover:border-white/10 hover:text-slate-400'
                }`}
              >
                {cat === 'all' ? 'All' : cat}
              </button>
            ))}
          </div>

          {/* Grid */}
          <div className="grid grid-cols-[repeat(auto-fill,minmax(148px,1fr))] gap-3 p-4">
            {products.map(product => (
              <ProductCard key={product.id} product={product} />
            ))}
          </div>
        </div>

        {/* Mini AI chat */}
        <div className="bg-[#1E293B]/80 border border-white/5 rounded-xl flex flex-col overflow-hidden" style={{ maxHeight: '520px' }}>
          <div className="flex items-center gap-2 px-4 py-3.5 border-b border-white/5 flex-shrink-0">
            <div className="w-[7px] h-[7px] rounded-full bg-cyan-500" />
            <h2 className="font-syne text-[13px] font-bold">AI Assistant</h2>
            <span className="ml-auto text-[11px] text-slate-500">Powered by OpenRouter</span>
          </div>
          <ChatWindow compact />
        </div>
      </div>
    </div>
  )
}
