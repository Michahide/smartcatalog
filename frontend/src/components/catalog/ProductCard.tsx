import { Star, Sparkles } from 'lucide-react'
import { Product } from '@/types'

interface Props {
  product: Product
  onClick?: (product: Product) => void
}

export default function ProductCard({ product, onClick }: Props) {
  const stars = Math.floor(product.rating)

  return (
    <div
      onClick={() => onClick?.(product)}
      className={`relative bg-[#1E293B] rounded-xl overflow-hidden cursor-pointer transition-all duration-200 hover:translate-y-[-2px] hover:shadow-lg border ${
        product.is_recommended
          ? 'border-violet-500/40'
          : 'border-white/5 hover:border-white/10'
      }`}
    >
      {product.is_recommended && (
        <div className="absolute top-2 right-2 flex items-center gap-1 bg-violet-600 text-white text-[9px] px-1.5 py-0.5 rounded-full font-bold z-10">
          <Sparkles className="w-2.5 h-2.5" />
          AI PICK
        </div>
      )}

      {/* Product image area */}
      <div className="w-full h-[80px] bg-[#162032] flex items-center justify-center text-[32px]">
        {product.emoji}
      </div>

      {/* Product info */}
      <div className="p-3">
        <p className="text-[12px] font-medium text-slate-300 truncate">{product.name}</p>
        <p className="text-[10px] text-slate-500 mt-0.5 mb-2">{product.category}</p>
        <p className="font-syne text-[13px] font-bold text-white">{product.price_formatted}</p>
        <div className="flex items-center gap-1 mt-1.5">
          <div className="flex text-amber-400 text-[10px]">
            {Array.from({ length: 5 }).map((_, i) => (
              <span key={i}>{i < stars ? '★' : '☆'}</span>
            ))}
          </div>
          <span className="text-[10px] text-slate-500">{product.rating}</span>
        </div>
      </div>
    </div>
  )
}
