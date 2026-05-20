# SmartCatalog — AI-Powered Web 4.0 Demo App

Aplikasi demo fullstack untuk seminar **"Building Intelligent Web with Fullstack Development"**.

```
Tech Stack:
  Frontend  →  Next.js 14 (App Router) + Tailwind CSS + TanStack Query
  Backend   →  Laravel 11 + Laravel Sanctum (Bearer Token)
  Database  →  MySQL 8 + Redis 7
  AI        →  OpenRouter API (Llama, Gemma, Mistral, GPT-4, dll.)
  DevOps    →  Docker / Podman + Nginx + Supervisord
```

---

## Panduan

| File | Isi |
|---|---|
| `OPENROUTER_SETUP.md` | Cara daftar & dapat API key OpenRouter |
| `PODMAN_WINDOWS.md`   | Setup khusus Windows dengan Podman |

---

## Cara Menjalankan

### Docker Compose (Docker Desktop)

```powershell
# 1. Isi API key
copy .env.example .env
notepad .env        # isi OPENROUTER_API_KEY

# 2. Build & start
docker-compose up -d --build

# 3. Buka browser
#    Frontend → http://localhost:3000
#    Backend  → http://localhost:8000
```

### Podman Compose (Podman Desktop — Windows)

```powershell
# Install podman-compose jika belum ada
pip install podman-compose

# Pastikan podman machine berjalan
podman machine start

# Isi API key
copy .env.example .env
notepad .env        # isi OPENROUTER_API_KEY

# Build & start
podman-compose up -d --build
```

> Panduan lengkap Podman: lihat **`PODMAN_WINDOWS.md`**

---

## Akun Demo

```
Email   : michael@dipa.co.id
Password: password
```

---

## Perintah Berguna

```powershell
# Lihat status container
docker-compose ps           # Docker
podman-compose ps           # Podman

# Lihat logs
docker-compose logs -f backend
podman-compose logs -f backend

# Stop semua
docker-compose down
podman-compose down

# Reset total (hapus database)
docker-compose down -v
podman-compose down -v

# Rebuild setelah ada perubahan kode
docker-compose up -d --build backend
```

---

## Struktur Proyek

```
smartcatalog/
├── .env.example               ← Isi OPENROUTER_API_KEY di sini
├── docker-compose.yml         ← Kompatibel Docker & Podman
├── README.md
├── OPENROUTER_SETUP.md        ← Panduan API key
├── PODMAN_WINDOWS.md          ← Panduan Podman di Windows
│
├── frontend/                  ← Next.js 14
│   ├── src/app/
│   │   ├── catalog/           ← Dashboard + product grid
│   │   ├── chat/              ← AI chatbot
│   │   ├── search/            ← Semantic search
│   │   ├── analytics/         ← Charts & metrics
│   │   └── api/chat/route.ts  ← SSE proxy → OpenRouter
│   └── Dockerfile
│
├── backend/                   ← Laravel 11
│   ├── app/Http/Controllers/Api/
│   │   ├── ChatController.php      ← SSE → OpenRouter
│   │   ├── ProductController.php   ← CRUD + Redis cache
│   │   ├── SearchController.php    ← Keyword scoring
│   │   └── AnalyticsController.php
│   ├── config/services.php         ← openrouter.key config
│   ├── docker/
│   │   ├── nginx.conf         ← /tmp paths (rootless compatible)
│   │   └── supervisord.conf
│   └── Dockerfile
│
└── nginx/
    └── smartcatalog.conf      ← Production Nginx (non-Docker)
```

---

## API Endpoints

```
POST /api/auth/login           → Bearer token
POST /api/auth/register
GET  /api/products             → ?category=Electronics
GET  /api/products/recommended
POST /api/chat                 → SSE streaming AI
GET  /api/search?q=laptop
GET  /api/analytics
```
