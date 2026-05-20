# Panduan Integrasi OpenRouter AI

OpenRouter adalah gateway AI yang menyediakan akses ke ratusan model (Llama, Gemma, Mistral, GPT-4, Claude, dll.)
melalui satu API yang kompatibel dengan format OpenAI. Tersedia model **gratis** untuk keperluan demo.

---

## Langkah 1 — Daftar Akun OpenRouter

1. Buka **https://openrouter.ai**
2. Klik tombol **"Sign In"** di pojok kanan atas
3. Pilih salah satu metode login:
   - **Continue with Google**
   - **Continue with GitHub**
4. Setelah login, Anda akan masuk ke dashboard OpenRouter

---

## Langkah 2 — Buat API Key

1. Di sidebar kiri, klik **"Keys"**
   _(atau langsung ke https://openrouter.ai/keys)_

2. Klik tombol **"Create Key"**

3. Isi form:
   - **Name**: `SmartCatalog Demo` _(bebas, untuk identifikasi)_
   - **Credit limit**: kosongkan _(unlimited, tapi model gratis tidak butuh kredit)_

4. Klik **"Create"**

5. **Copy API key** yang muncul — formatnya:
   ```
   sk-....
   ```
   > ⚠️ Key hanya ditampilkan sekali. Simpan di tempat aman.

---

## Langkah 3 — Isi API Key di Project

### Jika menggunakan Docker

```bash
# Di folder root project (smartcatalog/)
cp .env.example .env
```

Edit file `.env`:
```env
OPENROUTER_API_KEY=sk-....
OPENROUTER_MODEL=meta-llama/llama-3.3-8b-instruct:free
```

Jalankan:
```bash
docker-compose up -d
```

---

### Jika menjalankan manual

**Frontend** — edit `frontend/.env.local`:
```env
OPENROUTER_API_KEY=sk-....
OPENROUTER_MODEL=meta-llama/llama-3.3-8b-instruct:free
NEXT_PUBLIC_APP_URL=http://localhost:3000
```

**Backend** — edit `backend/.env`:
```env
OPENROUTER_API_KEY=sk-....
OPENROUTER_MODEL=meta-llama/llama-3.3-8b-instruct:free
```

---

## Langkah 4 — Pilih Model (Opsional)

Buka **https://openrouter.ai/models** untuk melihat semua model.
Filter dengan tombol **"Free"** untuk model tanpa biaya.

| Model ID | Keterangan | Biaya |
|---|---|---|
| `meta-llama/llama-3.3-8b-instruct:free` | Llama 3.3 8B — default demo | **Gratis** |
| `google/gemma-3-27b-it:free` | Gemma 3 27B by Google | **Gratis** |
| `mistralai/mistral-7b-instruct:free` | Mistral 7B | **Gratis** |
| `deepseek/deepseek-r1:free` | DeepSeek R1 reasoning | **Gratis** |
| `openai/gpt-4o-mini` | GPT-4o Mini by OpenAI | Berbayar |
| `anthropic/claude-3.5-haiku` | Claude 3.5 Haiku | Berbayar |
| `google/gemini-2.0-flash-001` | Gemini 2.0 Flash | Berbayar |

Ganti model di `.env`:
```env
OPENROUTER_MODEL=google/gemma-3-27b-it:free
```

> Model gratis memiliki rate limit lebih rendah. Untuk demo seminar, model gratis sudah cukup.

---

## Langkah 5 — Top Up Kredit (Opsional, untuk model berbayar)

1. Di dashboard OpenRouter, klik **"Credits"** di sidebar
2. Klik **"Add Credits"**
3. Minimum top up: **$5** (±Rp 80.000)
4. Bayar dengan kartu kredit/debit atau crypto

> Untuk demo seminar dengan model gratis, **tidak perlu top up**.

---

## Cara Kerja Integrasi di SmartCatalog

```
[User] ketik pesan di browser
        ↓
[Next.js] POST /api/chat  (route.ts)
        ↓  mengirim ke OpenRouter
[OpenRouter API] https://openrouter.ai/api/v1/chat/completions
        ↓  memilih & menjalankan model AI
[LLM] (Llama / Gemma / dll) generate respons
        ↓  SSE streaming
[Next.js] forward stream ke browser
        ↓
[React] render teks secara progressif (streaming)
```

OpenRouter menggunakan format yang sama dengan OpenAI:

```json
// Request body
{
  "model": "meta-llama/llama-3.3-8b-instruct:free",
  "messages": [
    { "role": "system", "content": "Kamu adalah asisten..." },
    { "role": "user",   "content": "Rekomendasikan produk laptop" }
  ],
  "stream": true
}

// SSE Response (per chunk)
data: {"choices":[{"delta":{"content":"Berikut rekomendasi"}}]}
data: {"choices":[{"delta":{"content":" laptop terbaik..."}}]}
data: [DONE]
```

---

## Troubleshooting

| Error | Penyebab | Solusi |
|---|---|---|
| `401 Unauthorized` | API key salah atau tidak diisi | Cek `.env` — pastikan `OPENROUTER_API_KEY` diisi dengan benar |
| `402 Payment Required` | Model berbayar, kredit habis | Ganti ke model gratis (tambahkan `:free`) atau top up kredit |
| `429 Too Many Requests` | Rate limit model gratis terlampaui | Tunggu beberapa detik, atau upgrade ke model berbayar |
| `Model not found` | ID model salah | Cek ID model di https://openrouter.ai/models |
| Respons kosong / tidak streaming | Buffering aktif di Nginx | Pastikan `X-Accel-Buffering: no` di response header |

---

## Monitoring Penggunaan

Pantau penggunaan API di dashboard OpenRouter:
- **https://openrouter.ai/activity** — history semua request
- **https://openrouter.ai/credits** — sisa kredit

Setiap request menampilkan: model yang digunakan, jumlah token, biaya, dan latency.
