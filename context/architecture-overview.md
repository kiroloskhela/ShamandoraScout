# ShamandoraScout — Architecture Overview

## 2026-07-14 — Current system understanding

### What this system is

**ShamandoraScout** (also branded **AthanasiusScouts2024**) is a Laravel 10 monolith for Coptic scout organization management: people (scouts/leaders), hierarchical groups (Qetaa / Marhala / Group tree), public live-form enrolment, events, attendance, finance/bookings, inventory/custody, medicine, notifications (FCM), and WhatsApp/email password flows.

It is **not** a React/SPA product. The primary UI is **server-rendered Blade** with Tailwind (Vite) + Alpine.js, plus leftover Bootstrap / SB Admin 2 pages. A Sanctum-authenticated **JSON API** serves mobile/web clients for persons, custody, bookings, games, etc.

### High-level architecture

```
┌─────────────────┐     ┌──────────────────────┐     ┌─────────────┐
│ Mobile / SPA    │────▶│ Laravel API (Sanctum)│────▶│ MySQL       │
│ (external)      │     │ routes/api.php       │     │ schema.sql  │
└─────────────────┘     └──────────────────────┘     │ ~96 tables  │
                                                     └─────────────┘
┌─────────────────┐     ┌──────────────────────┐            │
│ Browser admins  │────▶│ Blade Web UI         │────────────┘
│                 │     │ routes/web.php       │
│                 │     │  └─ routes/web/*.php │
└─────────────────┘     │ checkAuth roles      │
                        └──────────┬───────────┘
                                   │ sync HTTP
                    ┌──────────────┼──────────────┐
                    ▼              ▼              ▼
              Brevo email   WhatsApp bridge   Firebase FCM
                            (Node :3000)      (kreait)
```

### Tech stack (confirmed)

| Layer | Technology |
|-------|------------|
| Backend | PHP 8.1+, Laravel 10 |
| Auth (web) | Session + custom `checkAuth:Role1\|Role2` middleware |
| Auth (API) | Laravel Sanctum + custom `token.expiry` middleware |
| DB | MySQL — legacy PascalCase schema + newer snake_case tables |
| ORM | Eloquent adoption growing — **21** models vs ~96 tables; `DB::table` / `DB::select` still common in legacy controllers |
| Frontend | Blade + Tailwind (Vite via `@vite` in main layout) + Alpine (unpkg) + jQuery/Select2 + Bootstrap leftovers |
| Assets | Vite configured; main layout uses `@vite` for CSS/JS |
| Jobs/queues | **3** jobs (`SendFcmNotification`, `SendPasswordResetLinkMail`, `SendWhatsAppCampaignMessage`); `QUEUE_CONNECTION` defaults to `sync` |
| Cache | **Unused** — file driver default; zero `Cache::` usage in `app/` |
| Deploy | GitHub Actions SSH → single VPS (`/var/www/shamandora`), php8.3-fpm + nginx |
| Alt deploy | `webhook.js` + `deploy.sh` → `/var/www/Scout` (different path, weak secret) |

### Code inventory (measured 2026-07-16)

| Asset | Count / size |
|-------|----------------|
| Controllers | **84** files, **~15,513** lines total |
| Largest controllers | `SeasonEventBookingFinanceController` 1,479; `LiveFormEnrolmentController` 779; `QetaaTreeController` 677 |
| Eloquent models | **21** |
| Services | **3** (`BrevoService`, `FcmService`, `WhatsAppBridgeClient`) |
| Jobs / Events / Listeners | **3** / **0** / **0** |
| Migrations | **27** (core schema lives in `schema.sql`) |
| Blade views | **252** |
| Blade components | **4** (`x-data-table`, `x-form-card`, `x-card-stat`, `x-calendar`) |
| Web routes | **501** definitions across `routes/web.php` + `routes/web/*.php` (8 domain partials) |
| API routes | **46** in `routes/api.php` |
| Automated tests | **24** test classes, **84** PHPUnit cases; `phpunit.xml` present |

### Domain modules (logical, not reflected in folders)

1. **Person / enrolment** — hub `PersonInformation` + ~20 satellite tables; parallel `NewUsersInformation` / waiting-list pipeline; liveform + migration
2. **Org hierarchy** — `GroupTable` tree (`IncludedUnderGroupID`), Qetaa, SanaMarhala, PersonGroup/Role
3. **Events & attendance** — SeasonEvent, Attendance (well-indexed), waiting lists
4. **Finance** — SeasonEventParticipantFinance + payments/receipts (newer, better constraints)
5. **Inventory / custody** — custody requests (good atomic approve pattern), inventory issue docs
6. **Medicine** — best concurrency patterns in codebase (`lockForUpdate` + transactions)
7. **Notifications** — FCM sync batches; WhatsApp bridge; Brevo email

### Auth model (important)

- Auth user model maps to **`PersonInformation`**, not Laravel’s stock `users` table (`User` model sets `protected $table = 'PersonInformation'`).
- Passwords live in `PersonSystemPassword`.
- Web authorization is **role-string middleware**, not Policies/Gates.
- API tokens often issued with ability `['*']`.

### What is already done well (preserve these)

- Newer modules (Medicine, Custody approve/reject, SeasonEvent finance unique keys, Attendance indexes) show correct transactional / uniqueness patterns.
- Prior security remediation comments in `routes/web.php` (`// SECURITY:`) removed some public delete routes.
- Passwords are hashed with `Hash::make` on storage paths reviewed.
- File uploads generally validate mime/size.
- Parameter binding is used consistently for most SQL — few classic SQLi holes.

### Assumptions for this investigation

1. Production traffic is currently modest (PersonInformation AUTO_INCREMENT ≈ 1688; Attendance AUTO_INCREMENT already high ≈ 98k) but enrolment seasons and mobile API usage can spike.
2. Workspace `.env` may differ from the live VPS `.env`; findings that depend on env are marked **Potential** unless also evidenced in code defaults.
3. “Frontend” means Blade/Alpine/jQuery, not a separate SPA in this repo.
4. Complete rewrite is **not** justified; incremental hardening + modular extraction is the path.
