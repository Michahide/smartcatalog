import { useQuery } from '@tanstack/react-query'
import api from '@/lib/api'
import { Product, PaginatedResponse } from '@/types'

export function useProducts(category?: string, search?: string) {
  return useQuery({
    queryKey: ['products', category, search],
    queryFn: async () => {
      const params: Record<string, string> = {}
      if (category && category !== 'all') params.category = category
      if (search) params.search = search
      const { data } = await api.get<PaginatedResponse<Product>>('/api/products', { params })
      return data
    },
  })
}

export function useRecommendedProducts() {
  return useQuery({
    queryKey: ['products', 'recommended'],
    queryFn: async () => {
      const { data } = await api.get<Product[]>('/api/products/recommended')
      return data
    },
  })
}

export function useProduct(id: number) {
  return useQuery({
    queryKey: ['product', id],
    queryFn: async () => {
      const { data } = await api.get<Product>(`/api/products/${id}`)
      return data
    },
    enabled: !!id,
  })
}
