# ShamandoraScout

Laravel 10 backend for Athanasius / Shamandora Scout operations (persons, liveform enrolment, attendance, event finance, custody, inventory, WhatsApp campaigns).

## Local setup

```bash
cp .env.example .env
# Fill DB_*, APP_KEY (php artisan key:generate), optional Sentry / WhatsApp / Google keys
composer install
npm install
php artisan migrate   # MySQL; PHPUnit uses sqlite :memory:
npm run build         # or npm run dev
php artisan serve
```

- Auth model: `PersonInformation` (`App\Models\User`)
- Public liveform: `/liveform` (gated by `AppSettings` `liveform_open`)
- Health probe: `GET /health` (also `/up`)

## Tests & style

```bash
./vendor/bin/phpunit
./vendor/bin/pint --test app/Domain app/Http/Requests app/Http/Resources app/Http/Controllers
```

CI (`.github/workflows/deploy.yml`) runs PHPUnit (sqlite), Pint on Domain/Requests/Resources + hot controllers, and `npm ci && npm run build`. A MySQL PHPUnit job also runs against a service container.

## Queues & observability

- Worker unit: `deploy/laravel-queue.service`
- Failed-job alert: `php artisan queue:report-failed` (hourly via scheduler — ensure `* * * * * php artisan schedule:run` on the VPS)
- Logs: prefer `LOG_CHANNEL=daily` (14 days)
- Sentry: set `SENTRY_LARAVEL_DSN` and `SENTRY_RELEASE` (deploy writes release SHA)

## Deployment

Deploys to the VPS are handled by GitHub Actions on every push to **`main`**. Workflow: feature → **`testing`** → approve → **`main`**.

See [context/deploy-runbook.md](context/deploy-runbook.md) for rollback and health checks.

Schema source of truth: Laravel migrations under `database/migrations/`. `schema.sql` is a bootstrap snapshot and may lag; prefer `php artisan migrate`.
