// next.config.ts
import type { NextConfig } from 'next'

const nextConfig: NextConfig = {
  basePath: '/next',
  output: 'standalone',
  reactStrictMode: true,
  webpack: (config, { dev, isServer }) => {
    if (dev && !isServer) {
      config.watchOptions = {
        poll: 1000,
        aggregateTimeout: 300,
        ignored: /node_modules/
      }
    }
    return config
  },
}

export default nextConfig