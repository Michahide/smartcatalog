import type { NextConfig } from 'next'

const nextConfig: NextConfig = {
  // Allow standalone output for Docker
  output: 'standalone',

  // Proxy /api/chat → internal Next.js handler (no rewrite needed)
  // Backend API calls go through NEXT_PUBLIC_API_URL
  async rewrites() {
    return []
  },

  // Allow images from any hostname for product emoji/images
  images: {
    remotePatterns: [],
  },
}

export default nextConfig
