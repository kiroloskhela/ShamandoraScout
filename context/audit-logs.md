# System audit logs

SuperAdmin-only trail of **authenticated mutating** HTTP requests (`POST`, `PUT`, `PATCH`, `DELETE`).

## Components

| Piece | Path |
|-------|------|
| Migration | `database/migrations/2026_07_15_300001_create_audit_logs_table.php` |
| Model | `app/Models/AuditLog.php` (no `updated_at`) |
| Middleware | `app/Http/Middleware/LogAuthenticatedMutations.php` |
| UI | `GET /audit-logs` → `AuditLogController@index` |
| Tests | `tests/Feature/AuditLogMiddlewareTest.php` |

Middleware is registered at the **end** of both `web` and `api` groups in `app/Http/Kernel.php`.

## What is logged

- Actor: `person_id`, display name (`FirstName` / `SecondName` / `ThirdName`)
- Request: method, path, route name, derived `action` (`METHOD route|path`), IP, user agent
- Scrubbed JSON `request_payload` (keys matching password / token / secret / authorization / cookie / `_token` → `[redacted]`; truncated ~8KB)
- `response_status` via `app()->terminating(...)` after the response is built

## Skipped paths

- `audit-logs*`
- `_ignition*`
- `livewire*`
- `sanctum/csrf-cookie`

Unauthenticated requests and all `GET`/`HEAD`/`OPTIONS` are not logged.

## Deploy

```bash
php artisan migrate
```

Open SuperAdmin nav → إدارة كلمات المرور → سجل التدقيق.

## Out of scope

- Logging every read/GET
- Non-SuperAdmin access to the index
