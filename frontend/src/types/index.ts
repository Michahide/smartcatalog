export interface Product {
  id: number
  name: string
  slug: string
  category: string
  price: number
  price_formatted: string
  rating: number
  rating_count: number
  emoji: string
  description: string
  stock: number
  tags: string[]
  is_recommended: boolean
  created_at: string
}

export interface Category {
  id: number
  name: string
  slug: string
  emoji: string
}

export interface User {
  id: number
  name: string
  email: string
  avatar?: string
  preferences: string[]
}

export interface AuthResponse {
  user: User
  token: string
  token_type: string
}

export interface ChatMessage {
  role: 'user' | 'assistant'
  content: string
  created_at?: string
}

export interface SearchResult extends Product {
  relevance_score: number
}

export interface AnalyticsData {
  total_products: number
  ai_recommendations: number
  active_sessions: number
  revenue_today: number
  revenue_formatted: string
  sales_by_category: { category: string; percentage: number; color: string }[]
  traffic_sources: { source: string; percentage: number }[]
  ai_performance: {
    ctr: number
    revenue_lift: number
    accuracy: number
    avg_latency: number
  }
  activity_log: { icon: string; text: string; time: string; color: string }[]
}

export interface ApiError {
  message: string
  errors?: Record<string, string[]>
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}
