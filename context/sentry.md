# Sentry (Wave 5)

Laravel package: `sentry/sentry-laravel`.

## Enable on production

1. Create a project at https://sentry.io (or self-hosted).
2. Add to VPS `/var/www/shamandora/.env`:

```env
SENTRY_LARAVEL_DSN=https://<key>@o<org>.ingest.sentry.io/<project>
SENTRY_TRACES_SAMPLE_RATE=0.1
SENTRY_ENVIRONMENT=production
```

3. Clear config cache:

```bash
cd /var/www/shamandora && php artisan config:cache
```

With an empty DSN, Sentry is a no-op (safe to deploy before the key exists).

## Verify

```bash
php artisan sentry:test
```

## Notes

- Exceptions are also reported from `App\Exceptions\Handler`.
- Do not enable `send_default_pii` unless required (passwords / national IDs must not leave the VPS).
