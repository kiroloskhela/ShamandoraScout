# Backend Refactor Wave Status

## 2026-07-15 — Wave 2 complete on `testing` (awaiting main merge approval)

### Integration

- Branch: `testing` (tip includes liveform NewUsers ID fix)
- Umbrella PR: https://github.com/kiroloskhela/ShamandoraScout/pull/18 (**do not merge to main without approval**)
- PHPUnit on `testing`: **32 tests, 86 assertions — OK** (1 known deprecation from deprecated `Person` model)

### Waves landed on `testing`

| Wave | Scope | Status on `testing` |
|------|--------|---------------------|
| 0 | Context docs + security hotfixes branch | Done |
| 1 | Security (IDOR, Games authz, throttle, webhook removal, password WA, phpunit/CI) | Done |
| 2 | DB Packages A–E + MySQL/sqlite guards + Eloquent core models + liveform NewUsers IDs | **Done** |
| 3 | Domain services (enrolment / person API / org tree) + pagination | Done (earlier in same integration) |

### Task PRs (each cut from `main`; integrated into `testing`)

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
| 9–13 | `fix/db-*` | Packages A–E |
| 14–17 | `refactor-*` | Domain services + pagination |
| 18 | `testing` | Integration umbrella |
| 19 | `fix/db-migrations-mysql-guard` | Skip Package A–E DDL on non-MySQL |
| 20 | `fix/db-eloquent-core-models` | Eloquent models for hardened core tables |
| 21 | `fix/db-liveform-newusers-ids` | Liveform NewUsers PersonID via surrogate `id` |

### Wave 2 deliverables (detail)

- **Package A–E migrations** with `isMySql()` no-op for sqlite/CI
- **`scout:audit-identity-duplicates`** before UNIQUE migrations
- **Stop `MAX(PersonID)+1`** on PersonInformation (enrolment service + Excel import) and NewUsers liveform (`NewEnrolmentIdentity` + `allocateNewEnrolmentRecord`)
- **Eloquent models**: `Group`, `PersonGroup`, `PersonRole`, `Qetaa`, `PersonQetaa`, `SanaMarhala`, `PersonSanaMarhala`, `NewUserEnrolment`; `Roles` no longer `Authenticatable`; `Person` deprecated in favor of `User`
- **Waiting-list promote** strips surrogate `id` so Package A PKs do not collide

### FLAGS still needing human confirmation

1. **Games manage roles** assumed `SuperAdmin|AdminQetaa` — confirm if more roles needed.
2. **DB unique migrations** require `php artisan scout:audit-identity-duplicates` clean on each env before migrate.
3. **Admin password WhatsApp** no longer includes plaintext; share out-of-band (forgot-password email still carries a temp password — no reset-link route yet).
4. **Liveform dual-path**: `NewEnrolmentIdentity` still falls back to locked `MAX+1` until Package A is confirmed live everywhere; then simplify.
5. **Waiting-list → NewUsers migrate** may leave `PersonID != id` (keeps business PersonID for question FKs).
6. **CodeRabbit** leftovers on open task PRs before any `testing` → `main` merge.
7. **Ops**: stop any live `webhook.js` process on VPS if still running.

### Not merged to `main`

Per plan: stop here until you explicitly approve.
