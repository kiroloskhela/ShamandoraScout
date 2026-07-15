# Backend Refactor Wave Status

## 2026-07-15 — Waves 0–4 + Pre–5 done; remaining ops / Wave 5 in progress

### Done on `main`

| Wave | Scope | Status |
|------|--------|--------|
| 0–1 | Docs + security | Done |
| 2 | DB Packages A–E, Eloquent, liveform IDs | Done |
| 3 | Domain services + early pagination | Done |
| 4 | Integrity leftovers, split PersonNew, pagination, Policies p1, async jobs, Vite | Done |
| Pre–5 | Audit logs + WhatsApp bridge + campaigns | Done |

### Remaining checklist (working through)

| # | Item | Status |
|---|------|--------|
| 1 | VPS `laravel-queue.service` via deploy | **Done** |
| 2 | Waiting-list → NewUsers `PersonID != id` | **Done** |
| 3 | Policies phase 2 (Games model + Person API) | **Done** |
| 4 | Wave 5 — Sentry | **Done** (set `SENTRY_LARAVEL_DSN` on VPS) |
| 5 | Wave 5 — Redis | **Done** (set cache/session drivers on VPS after Redis up) |
| 6 | Dead SB Admin views / route cleanup | **Done** |

### Ops notes

- Queue: `systemctl status laravel-queue.service` after deploy; `.env` should have `QUEUE_CONNECTION=database`
- Repair mismatches: `php artisan enrolment:repair-person-ids` (+ `--waiting-list`, `--dry-run`)
- Sentry: `context/sentry.md` — add DSN then `php artisan config:cache`
- Redis: `context/redis.md` — deploy installs `redis-server`; flip `CACHE_DRIVER`/`SESSION_DRIVER` in `.env` when ready
- Docs: `context/async-queue-wave4.md`, `context/whatsapp-bridge.md`, `context/audit-logs.md`, `context/sentry.md`, `context/redis.md`
