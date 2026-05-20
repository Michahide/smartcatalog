import { useQuery } from '@tanstack/react-query'
import api from '@/lib/api'
import { AnalyticsData } from '@/types'

export function useAnalytics() {
  return useQuery({
    queryKey: ['analytics'],
    queryFn: async () => {
      const { data } = await api.get<AnalyticsData>('/api/analytics')
      return data
    },
    refetchInterval: 30_000, // live refresh every 30s
  })
}
