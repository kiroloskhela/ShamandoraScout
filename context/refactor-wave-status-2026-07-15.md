# Backend Refactor Wave Status

## 2026-07-15 — Execution complete (awaiting main merge approval)

### Integration

- Branch: `testing`
- PR: https://github.com/kiroloskhela/ShamandoraScout/pull/18 (**do not merge to main without approval**)
- PHPUnit on `testing`: **7 tests, 11 assertions — OK**

### Task PRs (each from `main`)

| PR | Branch | Topic |
|----|--------|-------|
| 1 | `refactor` | Context docs |
| 2 | `fix/security-hotfixes` | Hash passwords, remove public deletes, bind SQL |
| 3 | `fix/web-login-throttle` | Web login/forgot throttle |
| 4 | `fix/remove-deploy-webhook` | Remove webhook.js |
| 5 | `fix/api-person-idor` | Auth PersonID only |
| 6 | `fix/password-reset-no-plaintext` | No WA plaintext passwords |
| 7 | `fix/games-api-authz` | Games write roles |
| 8 | `fix/phpunit-bootstrap` | phpunit.xml + CI |
| 9 | `fix/db-identity-enrolment` | Package A |
| 10–13 | `fix/db-*` | Packages B–E |
| 14–17 | `refactor-*` | Domain services + pagination |
| 18 | `testing` | Integration |

### FLAGS still needing human confirmation

1. **Games manage roles** assumed `SuperAdmin|AdminQetaa` — confirm if more roles needed.
2. **DB unique migrations** require `php artisan scout:audit-identity-duplicates` clean on each env before migrate.
3. **Admin password WhatsApp** no longer includes plaintext; admin who typed the password must share it out-of-band (or use forgot-password email path).
4. **CodeRabbit** reviews were triggered on PRs; continue resolving any remaining line comments on individual PRs before merging `testing` → `main`.

### Not merged to `main`

Per plan: stop here until you approve.
