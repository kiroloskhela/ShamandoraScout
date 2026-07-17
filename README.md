<p align="center">
  <img src="public/img/shamandora.png" alt="Shamandora Scout" width="160" />
</p>

<h1 align="center">ShamandoraScout</h1>

<p align="center">
  <strong>Operations platform for Athanasius / Shamandora Scout</strong><br />
  People · Liveform enrolment · Attendance · Event finance · Custody · Inventory · WhatsApp
</p>

<p align="center">
  <a href="#-stack"><img src="https://img.shields.io/badge/Laravel-10-red?style=flat-square" alt="Laravel 10" /></a>
  <a href="#-stack"><img src="https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=flat-square" alt="PHP" /></a>
  <a href="#-quality--ci"><img src="https://img.shields.io/badge/tests-150%2B-brightgreen?style=flat-square" alt="Tests" /></a>
  <a href="#-ops--deploy"><img src="https://img.shields.io/badge/deploy-GitHub%20Actions%20→%20VPS-blue?style=flat-square" alt="Deploy" /></a>
  <a href="https://shamandorascout.com/health"><img src="https://img.shields.io/badge/health-/health-success?style=flat-square" alt="Health" /></a>
</p>

---

## Overview

**ShamandoraScout** is the Laravel backend and Blade admin UI that runs day-to-day scout operations: member records, public liveform enrolment, attendance, season-event booking finance, custody requests, inventory, medicine stock, and WhatsApp campaigns.

| Surface | What it does |
|---------|----------------|
| **Admin (Blade)** | Role-gated ops UI — Tailwind + Alpine, bilingual (AR/EN), dark mode on core screens |
| **JSON API** | Sanctum-authenticated mobile/API clients — persons, attendance, custody, place bookings, games, special cases |
| **Public liveform** | `/liveform` enrolment pipeline (capacity + waiting list), gated by app settings |

Auth identity is **`PersonInformation`** (`App\Models\User`), not Laravel’s default `users` table.

---

## Highlights

- **Security hardened** — person API IDOR fixes, attendance allow-lists, SuperAdmin-gated games/curricula writes, liveform resume removed (`410`)
- **Domain layer** — Event finance & liveform enrolment logic live in `app/Domain` (controllers stay thin HTTP adapters)
- **API contracts** — JsonResources + Form Requests on the hot mutating APIs
- **Ops-ready** — `GET /health`, daily logs, queue worker + scheduler, post-deploy health gate, Sentry release SHA
- **CI → production** — feature → `testing` → `main` → GitHub Actions SSH deploy

---

## Stack

| Layer | Tech |
|-------|------|
| Backend | PHP 8.1+, Laravel 10 |
| Web UI | Blade, Tailwind (Vite), Alpine.js |
| Auth (web) | Session + `checkAuth` role middleware |
| Auth (API) | Laravel Sanctum + token expiry |
| DB | MySQL (legacy PascalCase + newer tables) |
| Jobs | Database queue (`deploy/laravel-queue.service`) |
| Integrations | Brevo email, WhatsApp bridge, Firebase FCM, optional Google Drive / Sentry |

---

## Local setup

```bash
cp .env.example .env
# Fill DB_*, run: php artisan key:generate
# Optional: Sentry, WhatsApp, Google, Firebase keys

composer install
npm install
php artisan migrate          # MySQL; PHPUnit uses sqlite :memory:
npm run build                # or: npm run dev
php artisan serve
```

| Path | Purpose |
|------|---------|
| `/` | Admin (auth required) |
| `/liveform` | Public enrolment (when `liveform_open`) |
| `/health` · `/up` | Readiness probe (minimal public JSON) |
| `/health?token=…` | Ops details when `HEALTH_TOKEN` is set |

Dark-theme logo asset: `public/img/shamandora-dark.png` (light: `public/img/shamandora.png`).

---

## Quality & CI

```bash
./vendor/bin/phpunit
./vendor/bin/pint --test app/Domain app/Http/Requests app/Http/Resources app/Policies
```

GitHub Actions (`.github/workflows/deploy.yml`) on every `main` push:

1. Pint (scoped allowlist)  
2. PHPUnit (sqlite full suite)  
3. MySQL smoke suite  
4. Vite production build  
5. SSH deploy + migrate + config cache + queue/scheduler + **post-deploy health check**

Manual VPS audit (token + AUTO_INCREMENT):  
`gh workflow run ops-vps-checks.yml`

---

## Ops & deploy

| Concern | Location |
|---------|----------|
| Queue worker | `deploy/laravel-queue.service` |
| Scheduler cron | `deploy/laravel-scheduler.cron` |
| Failed jobs | `php artisan queue:report-failed` (hourly) |
| Logs | `LOG_CHANNEL=daily` (14-day retention) |
| Release | `SENTRY_RELEASE` written on deploy |

**Branch flow:** `feature/*` → **`testing`** → approve → **`main`** (auto-deploy).

Rollback & probes: [context/deploy-runbook.md](context/deploy-runbook.md)  
Architecture notes: [context/architecture-overview.md](context/architecture-overview.md)

Schema source of truth: Laravel migrations under `database/migrations/`. Prefer `php artisan migrate` over stale SQL dumps.

---

## Project layout (short)

```
app/Domain/          # Enrolment, EventFinance, Custody, PlaceBooking, …
app/Http/Controllers # Web + API
app/Http/Resources   # API JsonResources
app/Http/Requests    # Form Requests
resources/views      # Blade UI
deploy/              # systemd, cron, health check script
.github/workflows/   # CI + deploy + VPS ops checks
```

---

<p align="center">
  <img src="public/img/shamandora-dark.png" alt="Shamandora Scout (dark)" width="120" /><br />
  <sub>Built for Shamandora Scout · Athanasius</sub>
</p>
