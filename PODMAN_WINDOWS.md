# Running SmartCatalog on Windows with Podman

Podman is a rootless, daemonless alternative to Docker.
This guide covers setup specifically for **Windows + Podman Desktop / podman-machine**.

---

## Prerequisites

Make sure these are installed:

| Tool | Download |
|---|---|
| Podman Desktop | https://podman-desktop.io |
| podman-compose | `pip install podman-compose` |

Verify everything works:
```powershell
podman --version          # should show 4.x or 5.x
podman-compose --version  # should show 1.x
podman machine start      # start the Linux VM
```

---

## Step 1 — Clone / Extract the project

```powershell
cd C:\GitHub
# Extract smartcatalog.zip here so you have C:\GitHub\smartcatalog\
```

---

## Step 2 — Create your .env file

```powershell
cd C:\GitHub\smartcatalog
Copy-Item .env.example .env
notepad .env
```

Edit `.env` and fill in your OpenRouter API key:
```env
OPENROUTER_API_KEY=sk-or-v1-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
OPENROUTER_MODEL=meta-llama/llama-3.3-8b-instruct:free
```

> How to get an API key: see **OPENROUTER_SETUP.md**

---

## Step 3 — Build and start all services

```powershell
podman-compose up -d --build
```

This will:
1. Build the Laravel backend image (installs composer deps)
2. Build the Next.js frontend image
3. Start MySQL, Redis, Backend, Frontend containers
4. Run `php artisan migrate` + `db:seed` automatically

First build takes **3–5 minutes** (downloading base images + installing packages).

---

## Step 4 — Open in browser

| Service | URL |
|---|---|
| Frontend (Next.js) | http://localhost:3000 |
| Backend API (Laravel) | http://localhost:8000 |

**Demo login:** `michael@dipa.co.id` / `password`

---

## Useful Commands

```powershell
# See running containers
podman ps

# See logs of a specific service
podman-compose logs backend
podman-compose logs frontend
podman-compose logs mysql

# Follow logs in real time
podman-compose logs -f backend

# Stop all services
podman-compose down

# Stop and delete the database volume (full reset)
podman-compose down -v

# Rebuild after code changes
podman-compose up -d --build backend
podman-compose up -d --build frontend

# Open a shell inside the backend container
podman exec -it smartcatalog_backend_1 sh

# Run artisan commands manually
podman exec -it smartcatalog_backend_1 php artisan migrate:status
podman exec -it smartcatalog_backend_1 php artisan db:seed
```

---

## Troubleshooting

### Error: "Cannot connect to Podman"
```powershell
podman machine start
```

### Error: "port already in use"
Another process is using port 3000 or 8000. Either stop that process or change the ports in `docker-compose.yml`:
```yaml
ports:
  - "3001:3000"  # change host port
```

### Error: "permission denied" on storage/
This is the rootless UID mapping issue. Fixed in the current Dockerfile with `chown -R 82:82`.
If it still happens, run:
```powershell
podman exec -it smartcatalog_backend_1 chmod -R 777 /var/www/html/storage
```

### Error: backend exits immediately after starting
The database might not be ready yet. Check logs:
```powershell
podman-compose logs backend
```
If you see "Connection refused" to MySQL, the healthcheck retries should handle it.
If it keeps failing, increase `retries` in `docker-compose.yml` for the mysql healthcheck.

### AI chat not working (no response)
- Verify `OPENROUTER_API_KEY` is set in `.env`
- Restart containers after editing `.env`:
  ```powershell
  podman-compose down
  podman-compose up -d
  ```
- Test the API key directly:
  ```powershell
  curl -H "Authorization: Bearer sk-or-v1-xxx" https://openrouter.ai/api/v1/models
  ```

### Frontend shows "Network Error" when chatting
The frontend calls the backend at `http://localhost:8000`.
Make sure the backend container is healthy:
```powershell
podman ps  # backend should show "healthy" or "running"
```

---

## Alternative: podman-compose via pip

If `podman-compose` is not installed:
```powershell
pip install podman-compose
# or
pip3 install podman-compose
```

Then use it exactly like `docker-compose`:
```powershell
podman-compose up -d --build
podman-compose down
podman-compose logs -f
```

---

## Alternative: Use Docker Compose with Podman socket

Podman can expose a Docker-compatible socket, allowing regular `docker-compose` to work:

```powershell
# Enable Podman socket (run once)
podman machine ssh "sudo systemctl enable --now podman.socket"

# Then use docker-compose normally
$env:DOCKER_HOST = "npipe:////./pipe/podman-default-machine-socket"
docker-compose up -d --build
```
