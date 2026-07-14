# Backend Refactor Wave Status

## 2026-07-15 — Waves 0–4 complete; Pre–Wave 5 (audit + WhatsApp) on `testing`

### Integration

- Branch: `testing`
- Umbrella PR: https://github.com/kiroloskhela/ShamandoraScout/pull/18 (**do not merge to main without approval**)
- Feature PR (audit): https://github.com/kiroloskhela/ShamandoraScout/pull/22

### Waves landed on `testing`

| Wave | Scope | Status |
|------|--------|--------|
| 0–1 | Docs + security | Done |
| 2 | DB Packages A–E, Eloquent, liveform IDs | Done |
| 3 | Domain services + early pagination | Done |
| **4** | Integrity leftovers, split PersonNew, pagination, Policies, async jobs, Vite | **Done** |
| **Pre–5** | System audit logs + persistent Baileys WhatsApp bridge | **Done** |

### Pre–Wave 5 checklist

| Package | Branch | Topic | Docs |
|---------|--------|-------|------|
| Audit logs | `feat/audit-logs` | Middleware logs authenticated mutations; SuperAdmin `/audit-logs` | `context/audit-logs.md` |
| WhatsApp bridge | `feat/whatsapp-baileys-bridge` | In-repo Baileys + LocalAuth; SuperAdmin `/whatsapp/status` | `context/whatsapp-bridge.md` |

### Wave 4 packages

| Package | Branch | Topic |
|---------|--------|-------|
| Housekeeping | `testing` | Password-reset UI polish; WhatsApp removed from forgot-password |
| B | `fix/enrolment-capacity-atomic` | Atomic liveform capacity; Games AUTO_INCREMENT |
| A | `refactor-split-person-new-controller` | Split PersonNewController + LiveForm/WaitingList services |
| C | `refactor-paginate-hot-lists` | Server-side pagination (`SqlPaginator`) |
| F | `refactor-auth-policies-phase1` | GamePolicy / PersonPolicy + Gates |
| D | `refactor-async-notification-jobs` | Database queue Jobs for Brevo + FCM |
| E | `refactor-vite-main-layout` | Vite Tailwind/Alpine; fix relative vendor JS |

### Ops notes

- Run `php artisan queue:work` for async mail/FCM (see `context/async-queue-wave4.md`)
- Role matrix: `context/role-ability-matrix-wave4.md`
- Local/dev: `npm run build` (or `npm run dev`) required for `@vite` assets
- **Audit:** `php artisan migrate` for `audit_logs`
- **WhatsApp:** run `whatsapp-bridge` via pm2/systemd; never delete `auth_session/` casually; see `context/whatsapp-bridge.md`

### FLAGS remaining

1. **Waiting-list → NewUsers** may leave `PersonID != id`
2. **CodeRabbit** leftovers before `testing` → `main`
3. VPS: configure `queue:work` supervisor when deploying async jobs
4. VPS: deploy and pair WhatsApp bridge once; backup `auth_session/`

### Next (Wave 5 — not started)

Sentry / Redis / other Wave 5 items — only after explicit go-ahead.

### Not merged to `main`

Per plan: stop here until you explicitly approve.
