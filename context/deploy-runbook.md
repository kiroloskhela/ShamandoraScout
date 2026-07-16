# Deploy & rollback runbook

## Environments

| Env | Branch | Host path | Trigger |
|-----|--------|-----------|---------|
| Production | `main` | `/var/www/shamandora` | GitHub Actions on push to `main` |
| Testing (pre-prod) | `testing` | same VPS workflow when approved → merge to `main` | Manual promotion |

There is no separate staging VPS yet. Treat `testing` as the integration branch; promote to `main` only after approval.

## Deploy path

Sole path: `.github/workflows/deploy.yml`

1. `test` job: Composer, Pint (scoped), PHPUnit, npm build  
2. On `main` push only: SSH deploy (fetch, reset hard to `origin/main`, composer, vite build, migrate, caches, queue worker)  
3. Deploy sets `SENTRY_RELEASE` to the deployed git SHA in `.env` when possible  

## Rollback (production)

```bash
ssh <vps>
cd /var/www/shamandora
# Note current SHA
git rev-parse HEAD
# Reset to previous known-good SHA from GitHub
git fetch --all --prune
git reset --hard <good-sha>
composer install --no-dev --optimize-autoloader --no-interaction
npm install && npm run build && npm prune --omit=dev || true
# Only if a bad migration shipped and is safely reversible:
# php artisan migrate:rollback --step=1 --force
php artisan optimize:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache
systemctl restart php8.3-fpm
systemctl reload nginx
systemctl restart laravel-queue.service
```

Prefer forward-fix migrations over rollback when data was written.

## Health

- App probe: `GET /health` (JSON; 503 if DB down)  
- Queue: `php artisan queue:report-failed` (scheduled hourly; logs + Sentry)

## Logs

Prefer `LOG_CHANNEL=daily` (14-day retention via `config/logging.php`).
