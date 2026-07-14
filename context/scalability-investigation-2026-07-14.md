# ShamandoraScout — Scalability & Architecture Investigation

**Date:** 2026-07-14  
**Scope:** Full repository (backend, frontend/Blade, database, APIs, deploy, security, testing)  
**Method:** Parallel codebase exploration + spot-verification of critical evidence in source and `schema.sql`  
**Status:** Findings are marked **Confirmed** or **Potential**

> Companion: [architecture-overview.md](./architecture-overview.md)

---

# Architecture summary (read this first)

ShamandoraScout is a **Laravel 10 monolith** for scout org operations. Admins use **Blade + Tailwind CDN + Alpine**; mobile/clients use a **Sanctum API**. Data access is almost entirely **raw Query Builder / SQL** against a **legacy MySQL schema** (`schema.sql` is source of truth; Laravel migrations cover only ~10 incremental tables). There is **no queue, no cache layer, no service/domain layer, almost no tests**, and deployment is **single-VPS push-to-main**.

At today’s data size (~1.7k persons) many list queries still “work.” At growth in users, seasons, attendance, concurrent liveform signups, and engineering team size, the confirmed IDOR/authz gaps, missing indexes, race conditions on enrolment, and god-controllers become the binding constraints — not “Laravel can’t scale.”

---

# 1. Backend and System Architecture Problems at Scale

Findings ranked by severity. Each entry includes problem, location, evidence, why it matters now, scale impact, severity, and fix.

---

## B1. IDOR — API trusts client-supplied user/person IDs

**Status:** Confirmed · **Severity:** Critical

1. **Problem:** Authenticated API callers can read other users’ rosters and any person’s PII by changing IDs in the request.
2. **Where:** `app/Http/Controllers/API/PersonApiController.php` — `showPersons`, `ShowProfile`, `ShowCalendar`; routes in `routes/api.php` under `auth:sanctum` + `token.expiry`. Related: `ExportController::exportScoutsExcel($userId)` (role-gated but not scope-gated).
3. **Evidence:**
```145:167:app/Http/Controllers/API/PersonApiController.php
public function showPersons(Request $request)
{
    $userId = (int) $request->input('id');
    // ...
    ->where('pg2.PersonID', $userId)
```
   `ShowProfile($id)` / `ShowCalendar($id)` load by path id with no ownership/group-scope check. Contrast: `CustodyApiController` correctly scopes by authenticated person.
4. **Why now:** Any leaked/stolen Sanctum token (or any legitimate low-privilege account) can scrape national IDs, phones, addresses, medical-adjacent entry answers.
5. **At scale:** Automated enumeration becomes trivial; GDPR/privacy and org trust failures; mobile API growth amplifies blast radius.
6. **Fix:** Never trust client `id` for authorization. Derive actor from `$request->user()`. Enforce Qetaa/Group scope (same rules as web `checkAuth` + org tree). Add policy helpers; tighten rate limits on PII endpoints; add feature tests for negative authz cases.

```php
// Pseudocode
$actorId = $request->user()->PersonID; // or getAuthIdentifier()
abort_unless($this->canViewPerson($actorId, $targetId), 403);
```

---

## B2. Missing authorization on Games API writes

**Status:** Confirmed · **Severity:** Critical

1. **Problem:** `hasAnyRole()` exists but is never called; any authenticated user can mutate Games.
2. **Where:** `app/Http/Controllers/API/GamesApiController.php` — `store`, `update`, `destroy`.
3. **Evidence:** Helper defined ~lines 23–32; write methods ~117–227 perform no role check.
4. **Why now:** Content integrity / privilege escalation via API.
5. **At scale:** More API clients → more accidental or malicious writes.
6. **Fix:** Call `hasAnyRole` (or better: Laravel Policies) on all mutating methods; deny by default.

---

## B3. Manual `MAX(id)+1` primary keys + concurrent migration races

**Status:** Confirmed · **Severity:** Critical

1. **Problem:** App bypasses AUTO_INCREMENT and computes next PersonID manually; one path lacks row locks; concurrent admin migrations can collide.
2. **Where:** `MigrateNewEnrolments.php` (~45–69); also `GamesApiController::nextGameId()`; liveform path in `PersonNewController` uses `lockForUpdate` but on poorly indexed tables.
3. **Evidence:**
```45:68:app/Http/Controllers/MigrateNewEnrolments.php
$lastPersonID = DB::table('PersonInformation')->orderBy('PersonID', 'desc')->first();
$thisPersonID = is_null($lastPersonID) ? 1 : ((int)$lastPersonID->PersonID + 1);
// ...
DB::table('PersonInformation')->insert(['PersonID' => $thisPersonID, ...]);
```
   Schema: `PersonInformation.PersonID` **is** `AUTO_INCREMENT` (`schema.sql` ~1070–1088).
4. **Why now:** Duplicate-key failures or partial batch migrations under concurrent use.
5. **At scale:** Parallel season migrations / multi-admin ops → intermittent data-loss *perception* (rows stuck in NewUsers) and support load.
6. **Fix:** Omit `PersonID` on insert; let AUTO_INCREMENT assign; generate `ShamandoraCode` from returned id. Use one transaction per batch with proper locks only where needed.

---

## B4. Liveform capacity TOCTOU + unindexed `lockForUpdate`

**Status:** Confirmed · **Severity:** Critical

1. **Problem:** Max-limit check is count-then-insert without atomic lock; `lockForUpdate` on `RaqamQawmy` without index can full-table-lock `NewUsersInformation`.
2. **Where:** `PersonNewController` liveform submit (~876–908, ~2017–2020); table `MarhalaLiveFormLimit` has **no PK/indexes** (`schema.sql` ~590–594); `NewUsersInformation` has **no PK** (~746+).
3. **Evidence:** Pattern: read `MaxLimit`, `count()`, decide waiting list, then insert — not atomic with capacity. `MarhalaLiveFormLimit` definition has no keys.
4. **Why now:** Over-enrolment past published limits; under load, signup latency/deadlocks.
5. **At scale:** Seasonal spikes (hundreds of concurrent liveform posts) serialize or overshoot.
6. **Fix:** Add PK/unique on `(QetaaID, SanaMarhalaID[, Year])`; unique index on `RaqamQawmy`; enforce capacity with transactional `SELECT … FOR UPDATE` on the limit row + conditional insert, or atomic counter column.

---

## B5. Missing unique indexes on national ID and ShamandoraCode

**Status:** Confirmed · **Severity:** High (Critical for data integrity under concurrency)

1. **Problem:** Logical unique identifiers are not unique in DB; uniqueness is only app-level and racy.
2. **Where:** `PersonInformation` (`schema.sql` ~1070–1088) — PK only; no UNIQUE on `RaqamQawmy` / `ShamandoraCode`. Same gap on `NewUsersInformation*`. Contrast: `Guests` / `FamilyMembers` **do** have unique `RaqamQawmy`.
3. **Evidence:** Table DDL ends with `PRIMARY KEY (PersonID)` only.
4. **Why now:** Duplicate persons possible if checks race or are bypassed.
5. **At scale:** Dedup/reporting/API identity break; tree lookups (`PersonTreeController`) full-scan on `RaqamQawmy`.
6. **Fix:** Backfill duplicates, then add UNIQUE indexes; update app to handle integrity exceptions cleanly.

---

## B6. Unauthenticated deploy webhook with hardcoded weak secret

**Status:** Confirmed · **Severity:** High

1. **Problem:** Anyone who can hit the Node webhook can trigger `deploy.sh`.
2. **Where:** `webhook.js` (secret `mysecretkey` in query string); `deploy.sh` targets `/var/www/Scout` while GH Actions deploys `/var/www/shamandora`.
3. **Evidence:**
```1:16:webhook.js
const SECRET = 'mysecretkey';
if (req.method === 'POST' && req.url === `/deploy?secret=${SECRET}`) {
  exec('/var/www/Scout/deploy.sh', ...
```
4. **Why now:** Remote code execution via redeploy of attacker-controlled repo content if secret leaks (URL logs/referrers).
5. **At scale:** More attackers probing; dual deploy paths cause env drift.
6. **Fix:** Delete `webhook.js` path; rely on GitHub Actions + SSH secrets; if webhook needed, verify GitHub HMAC signatures.

---

## B7. Plaintext passwords sent over WhatsApp; controller-to-controller coupling

**Status:** Confirmed · **Severity:** High

1. **Problem:** New passwords transmitted in cleartext via WhatsApp bridge; `AdminPasswordController` fabricates Requests to call `WhatsAppBridgeController` directly.
2. **Where:** `AdminPasswordController::update` (~31–75); similar in `ForgotPasswordController`.
3. **Evidence:** `'message' => "Your New Password Is: {$plain}"` then `app(WhatsAppBridgeController::class)->sendWithHeader($fake)`.
4. **Why now:** Credentials persist in WhatsApp history; bridge becomes a secrets channel.
5. **At scale:** More resets → more credential sprawl; harder compliance.
6. **Fix:** Send time-limited reset **link** only; force change-on-first-login; extract `WhatsAppNotifier` service; queue the send.

---

## B8. No pagination on hot list endpoints (~0 `paginate()` in controllers)

**Status:** Confirmed · **Severity:** High

1. **Problem:** Admin/API lists load entire result sets into memory.
2. **Where:** `PersonNewController::index` / `ShowPersons`; `AdminPasswordController::index`; `API\PersonApiController::showPersons`; `GroupPersonController::index`; broadly ~230 `->get()`/`->all()` patterns.
3. **Evidence:** Shell/grep found no meaningful `->paginate(` usage under `app/Http/Controllers`. Attendance table already large (AUTO_INCREMENT ~98k).
4. **Why now:** Latency and memory grow linearly with roster size; already painful on multi-join person lists.
5. **At scale:** PHP-FPM workers OOM / timeouts; DB CPU spikes; API mobile clients hang.
6. **Fix:** Server-side pagination + indexed sort keys; for API, cursor/page params; for exports, streamed Excel (chunked queries).

---

## B9. Recursive per-node SQL on unindexed group tree

**Status:** Confirmed · **Severity:** High

1. **Problem:** Group hierarchy walked with one query per node; `GroupTable.IncludedUnderGroupID` has no index; no cycle guard.
2. **Where:** `GroupPersonController::getNodesBelow`, `getParentsPathString`; similar in `QetaaTreeController`, `PersonTreeController`.
3. **Evidence:** `GroupTable` DDL (`schema.sql` ~424–430) — PK only. Recursive `DB::select` in loop.
4. **Why now:** Edit/create pages for Khadems issue O(nodes×depth) queries.
5. **At scale:** Tree growth → multi-second page loads; cycle → stack overflow.
6. **Fix:** Index `IncludedUnderGroupID`; load adjacency list once and walk in PHP; or nested sets/closure table; cycle detection.

---

## B10. Sync-only architecture — no jobs, queues, or background workers

**Status:** Confirmed · **Severity:** High

1. **Problem:** FCM, Brevo, WhatsApp all run in the HTTP request; `QUEUE_CONNECTION` defaults to `sync`; no `app/Jobs`.
2. **Where:** `NotificationController` + `FcmService`; `ForgotPasswordController` + `BrevoService`; WhatsApp bridge (`Http` with up to 20s timeout); `config/queue.php`.
3. **Evidence:** No Jobs directory; `.env` leaves queue/cache/session Redis settings commented; schema dump lacks usable jobs/sessions tables for DB drivers without migrations.
4. **Why now:** User-facing latency and partial failures when third parties slow down.
5. **At scale:** Broadcast notifications to hundreds of devices block workers; outages cascade to UX.
6. **Fix:** Enable Redis (already scaffolded in config); `ShouldQueue` jobs for FCM/email/WhatsApp; Supervisor `queue:work`; failed_jobs + retries/backoff.

---

## B11. Single-VPS, file sessions/cache, local disk — not horizontally scalable

**Status:** Confirmed · **Severity:** High (for growth) / Medium (today)

1. **Problem:** File sessions, file cache, local uploads, one app server; deploy is `git reset --hard` with no staging/tests.
2. **Where:** `config/session.php`, `config/cache.php`, `config/filesystems.php` defaults; `.github/workflows/deploy.yml`; `storage/app/public` usage.
3. **Evidence:** Deploy workflow restarts single `php8.3-fpm` on one host; no shared Redis/S3.
4. **Why now:** Fine for one server; session loss and upload inconsistency if a second node is added.
5. **At scale:** Cannot add app servers without sticky sessions or shared session store; deploys cause brief disruption.
6. **Fix:** Redis sessions/cache; S3/compatible object storage for uploads; blue/green or at least health-checked rolling restart; staging environment.

---

## B12. Schema not managed by migrations / env drift

**Status:** Confirmed · **Severity:** High (team scalability)

1. **Problem:** ~86 core tables only in `schema.sql`; `migrate:fresh` cannot rebuild app; migrations and dump already disagree (e.g. Documents columns).
2. **Where:** `database/migrations/` (10 files); `schema.sql`; `2026_01_08_000000_add_document_columns.php` uses `hasColumn` guards.
3. **Evidence:** Inventory counts; Feedback PascalCase vs snake_case `feedback` collision risk.
4. **Why now:** Onboarding and CI cannot reproduce DB; “works on my dump” bugs.
5. **At scale:** Multiple engineers → schema conflicts; production-only columns.
6. **Fix:** Baseline migration from dump (or document mandatory `schema.sql` import + versioned incremental migrations only); add schema lint in CI; resolve Feedback table naming.

---

## B13. Fat controllers / no domain services / tight coupling

**Status:** Confirmed · **Severity:** High (maintainability)

1. **Problem:** Business logic, SQL, validation, and HTTP mixed in 2k-line controllers; almost no service layer.
2. **Where:** `PersonNewController` (2221), `SeasonEventBookingFinanceController` (1663); only `BrevoService`/`FcmService` under `app/Services/`.
3. **Evidence:** Line counts; duplicate namespace/`use` block at top of `PersonNewController`; controller invoking controller for WhatsApp.
4. **Why now:** Changes risk regressions; hard to unit test.
5. **At scale:** Team parallel work causes merge conflicts and copy-paste bugs (already visible: duplicate routes, duplicate person-search JS).
6. **Fix:** Extract use-cases (`MigrateEnrolment`, `SubmitLiveform`, `BookSeasonEvent`) into services; keep controllers thin; do **not** rewrite all at once — peel hottest paths first.

---

## B14. Duplicate / conflicting routes

**Status:** Confirmed · **Severity:** Medium–High

1. **Problem:** Same URIs registered multiple times under different role groups; Laravel uses first match — authz becomes accidental.
2. **Where:** `routes/web.php` — `/person` thrice; `/event*` twice; comments admit MaxLimits duplication (~408, ~538).
3. **Evidence:** File comments and duplicate definitions (~884 lines, ~430+ routes).
4. **Why now:** Wrong middleware may apply silently.
5. **At scale:** More features → more ghost routes; security regressions.
6. **Fix:** Deduplicate; use single role middleware sets; `php artisan route:list` CI check for duplicates.

---

## B15. Booking check-then-insert races (partially mitigated)

**Status:** Confirmed pattern · **Severity:** Medium

1. **Problem:** `exists()` then insert outside/at edge of transactions.
2. **Where:** `SeasonEventBookingFinanceController` (~572–645); waiting list store similarly.
3. **Evidence:** Unique keys on finance/waiting-list tables catch many races **after** the fact — safer than liveform, but UX is generic errors.
4. **Why now:** Duplicate attempts under double-submit.
5. **At scale:** More contention → more failed UX, support tickets.
6. **Fix:** Rely on unique constraints + catch DuplicateKey; or lock participant row; improve user messaging (pattern already good in `AdminCustodyRequestController` conditional update).

---

## B16. Weak web login throttling / weak password policy

**Status:** Confirmed · **Severity:** High (security)

1. **Problem:** API login throttled; **web** `/login` is not; passwords `min:6`.
2. **Where:** `routes/web.php` login routes; `RouteServiceProvider` api limiter only; `AdminPasswordController` validation.
3. **Evidence:** `throttle:5,1` on `/api/login` only.
4. **Why now:** Online guessing against short passwords.
5. **At scale:** Credential stuffing as user count grows.
6. **Fix:** `throttle` on web login + forgot-password; lockout; stronger password rules; optional 2FA for admins.

---

## B17. No caching; role lookup every request

**Status:** Confirmed · **Severity:** Medium

1. **Problem:** Zero application cache; auth middleware loads roles per request.
2. **Where:** `CheckAuthentication`; no `Cache::` in `app/`.
3. **Evidence:** Grep empty for Cache usage; middleware relation load.
4. **Why now:** Extra DB round-trip per page.
5. **At scale:** Reference data (Qetaa, Marhala, limits) repeatedly hit DB.
6. **Fix:** Redis cache for roles (short TTL), lookup tables, tree snapshots invalidated on write.

---

## B18. Observability gaps

**Status:** Confirmed · **Severity:** Medium–High (ops)

1. **Problem:** Single log file, no APM/error tracker, no health endpoint, PII sometimes logged (`Log::info("Request: " . $request)` in person index).
2. **Where:** `config/logging.php`; `app/Exceptions/Handler.php` empty reportable; `PersonNewController` logging; no Sentry.
3. **Evidence:** Stock handler; `single` channel; schedule only `sanctum:prune-expired`.
4. **Why now:** Production incidents diagnosed slowly; PII in logs.
5. **At scale:** Cannot find slow queries or error spikes across seasons.
6. **Fix:** `daily` logs; Sentry; request IDs; `/up` health (DB+Redis+queue); stop logging full Request objects; slow-query log.

---

## B19. Almost zero automated tests / no phpunit.xml / deploy without test gate

**Status:** Confirmed · **Severity:** High (team + reliability)

1. **Problem:** Two Example tests; CI deploys on push with no test step.
2. **Where:** `tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php`; `.github/workflows/deploy.yml`.
3. **Evidence:** No `phpunit.xml` in repo; deploy script only composer/npm/artisan cache.
4. **Why now:** Refactors and security fixes are unverifiable.
5. **At scale:** Fear slows delivery; regressions in finance/enrolment are costly.
6. **Fix:** Restore `phpunit.xml`; Feature tests for authz/IDOR, liveform capacity, booking uniqueness, password flows; make deploy job depend on green tests.

---

## B20. API design gaps (versioning, abilities, Swagger exposure)

**Status:** Confirmed / Potential · **Severity:** Medium

1. **Problem:** No `/v1` prefix; tokens with `['*']`; Swagger may be publicly documenting IDOR surfaces (`L5_SWAGGER_GENERATE_ALWAYS`).
2. **Where:** `routes/api.php`; `LoginApiController`; l5-swagger config/env.
3. **Evidence:** API_DOCUMENTATION.md documents client-supplied `id` as intended behavior.
4. **Why now:** Breaking mobile clients later is hard; docs advertise insecure contract.
5. **At scale:** Multiple app versions in the wild.
6. **Fix:** Version APIs; scoped token abilities; gate Swagger behind auth in production; rewrite API docs to authenticated-self scope.

---

## B21. Person delete orphan risk / incomplete cascade list

**Status:** Confirmed · **Severity:** Medium

1. **Problem:** Manual multi-table deletes in controller miss some Person* tables.
2. **Where:** `PersonNewController::destroy` (~1695–1715).
3. **Evidence:** Fixed list of ~10–11 deletes; `PersonImages` / `PersonGroup` etc. may be omitted depending on path.
4. **Why now:** Orphan rows / broken FKs.
5. **At scale:** More satellite tables → more silent orphans.
6. **Fix:** Prefer DB `ON DELETE CASCADE` where safe; single domain `PersonDeleter` service with exhaustive inventory test.

---

## B22. Hardcoded Qetaa age-band business rules

**Status:** Confirmed · **Severity:** Low–Medium

1. **Problem:** Magic QetaaID/SanaMarhalaID ranges in `LiveFormMaxLimitsController`.
2. **Where:** ~41–95 if/elseif ladder.
3. **Evidence:** `if($request->qetaa_id==1)` style branches.
4. **Why now:** Org rule changes require code deploy.
5. **At scale:** More qetaat → unmaintainable conditionals.
6. **Fix:** Data-driven rules table validated against `Qetaa`/`SanaMarhala`.

---

# 2. Frontend Problems at Scale

**Assumption:** Frontend = Blade + Alpine + jQuery in this repo (no first-party React SPA). Findings framed accordingly.

---

## F1. Tailwind Play CDN used in production layout

**Status:** Confirmed · **Severity:** Critical (perf/ops)

1. **Problem:** Browser JIT-compiles Tailwind on every page; Vite/Tailwind pipeline exists but main layout ignores it.
2. **Affected:** `resources/views/layouts/app.blade.php:52`; ~17 files similar; `package.json` / `vite.config.js` / `resources/css/app.css` unused by main layout.
3. **Evidence:** `<script src="https://cdn.tailwindcss.com"></script>`; no `@vite` in `layouts/app.blade.php`.
4. **Impact:** Slow first paint, console warnings, dependency on CDN, large runtime cost.
5. **Growth:** More pages/classes → worse client CPU; CDN outage breaks styling.
6. **Fix:** Compile via Vite; `@vite(['resources/css/app.css', 'resources/js/app.js'])`; safelist dynamic classes used by `x-form-card` color props.

---

## F2. Unpinned Alpine from unpkg

**Status:** Confirmed · **Severity:** High

1. **Problem:** `//unpkg.com/alpinejs` without version/SRI; Alpine powers sidebar + shared components.
2. **Affected:** `layouts/app.blade.php:72`; `x-data-table`, `x-form-card`.
3. **Evidence:** Unpinned script tag.
4. **Impact:** Breaking major release or unpkg outage takes down interactive UI.
5. **Growth:** More Alpine components → higher blast radius.
6. **Fix:** Pin version via npm; bundle with Vite; SRI if CDN kept temporarily.

---

## F3. Broken relative vendor JS paths on routed pages

**Status:** Confirmed · **Severity:** High

1. **Problem:** Scripts use `../vendor/jquery/jquery.min.js` etc.; `jquery.min.js` may not exist; paths break off non-root URLs.
2. **Affected:** `person/person-create.blade.php`, `betakat-takaddom/*`, `max-limits/delete.blade.php`, `person/entry-error-repeat-trial.blade.php`.
3. **Evidence:** Nested full HTML inside `@extends`; multiple conflicting jQuery includes.
4. **Impact:** Validation/UI JS silently fails on some routes.
5. **Growth:** More copy-paste pages → more latent 404s.
6. **Fix:** Root-absolute `/vendor/...` or Vite imports; remove nested HTML documents from extended layouts.

---

## F4. Three coexisting UI frameworks + ~6k lines dead template views

**Status:** Confirmed · **Severity:** High (team scalability)

1. **Problem:** Tailwind (primary), Bootstrap 5, SB Admin 2 Bootstrap 4 theme coexist; ~49 standalone full-HTML pages; orphan demo views (`buttons`, `cards`, `charts`, `utilities-*`, `index2`, etc.).
2. **Affected:** `resources/views/*` (~251 blades); `resources/css/sb-admin-2.css` (~11k lines).
3. **Evidence:** Grep shows no routes for many demo blades; dual layout eras.
4. **Impact:** Inconsistent UX; new engineers fix dead files; CSS conflicts.
5. **Growth:** Design debt compounds; impossible “one design system.”
6. **Fix:** Delete unrouted demo views; migrate high-traffic standalone pages onto `layouts.app`; deprecate SB Admin CSS when unused.

---

## F5. Duplicated person-search widgets (XSS inconsistency)

**Status:** Confirmed · **Severity:** High

1. **Problem:** Same autocomplete copied 10+ times; some escape HTML, some inject names via `innerHTML`.
2. **Affected:** `event_waiting_list/index.blade.php`, `personspecialcase/create.blade.php`, `medicine/dispense.blade.php`, `event_booking_finance/create.blade.php`, `group-person/create.blade.php`, `family-members/*`, `guests/create.blade.php`, etc.
3. **Evidence:** Local `escapeHtml` in one file vs raw template literals in another.
4. **Impact:** XSS if names contain HTML; maintenance tax on every search UX change.
5. **Growth:** N copies diverge further.
6. **Fix:** One Blade partial + one JS module (`resources/js/person-search.js`); always textContent/escape.

---

## F6. Monolithic Blade views

**Status:** Confirmed · **Severity:** Medium–High

1. **Problem:** Multi-thousand-line views mix PHP, markup, and inline handlers.
2. **Affected:** `tree/index.blade.php` (~2157), `person/person-create-liveform.blade.php` (~1565), `person-tree/index.blade.php` (~1174), `layouts/app.blade.php` (~801).
3. **Evidence:** Line counts; only one notable partial `tree/_group.blade.php`; 4 components total.
4. **Impact:** Unreviewable PRs; high conflict rate.
5. **Growth:** Feature additions bloat files further.
6. **Fix:** Split into components/partials; move JS to modules; keep `@php` out of views (move to ViewModels/controllers).

---

## F7. No frontend tests; fragile Alpine data-table

**Status:** Confirmed · **Severity:** Medium–High

1. **Problem:** Shared `x-data-table` (pagination/sort/filter/localStorage) has no tests; no Dusk/Playwright.
2. **Affected:** `resources/views/components/data-table.blade.php` (~566 lines), ~35 consumers.
3. **Evidence:** Only Laravel Example tests.
4. **Impact:** Regressions break most CRUD indexes at once.
5. **Growth:** More tables → higher cost of breakage.
6. **Fix:** Extract JS; unit-test sorting/filtering; 1–2 Playwright smoke flows (login, person list, liveform).

---

## F8. Inconsistent JS idioms (vanilla / jQuery / Alpine / inline onclick)

**Status:** Confirmed · **Severity:** Medium

1. **Problem:** Three+ patterns for the same problems; ~213 inline HTML event handlers.
2. **Affected:** Widespread; concentrated in `person-create.blade.php`, `tree/index.blade.php`.
3. **Evidence:** `onclick=` counts; Select2 via jQuery in event create/edit; fetch elsewhere.
4. **Impact:** Onboarding friction; hard to share helpers.
5. **Growth:** Team size increases style fragmentation.
6. **Fix:** Convention doc: Alpine for UI state, fetch modules for AJAX, ban new inline handlers.

---

## F9. Dynamic Tailwind class interpolation incompatible with future JIT build

**Status:** Confirmed · **Severity:** Medium (latent)

1. **Problem:** `border-{{ $submitColor }}-300` works with CDN JIT scanning DOM; breaks under compiled Tailwind without safelist.
2. **Affected:** `x-form-card`, `x-card-stat`.
3. **Evidence:** String-built utility classes in components.
4. **Impact:** Migrating off CDN can silently unstyle buttons/cards.
5. **Growth:** More dynamic props → more missing classes.
6. **Fix:** Map colors to full class strings in PHP, or Tailwind safelist.

---

## F10. Accessibility & loading-state inconsistency

**Status:** Confirmed · **Severity:** Low–Medium

1. **Problem:** Newer forms have labels; older pages use placeholder-as-label; AJAX widgets have bespoke error UI.
2. **Affected:** Legacy `person-create` vs `login`; global overlay in `layouts/app.blade.php` (good) vs local widgets.
3. **Evidence:** Mixed `aria-*` usage; duplicated loading overlay comment.
4. **Impact:** Screen-reader / mobile usability uneven.
5. **Growth:** Inclusive-access requirements harder to meet retroactively.
6. **Fix:** Form component standards; shared toast/error partial.

---

## F11. CSRF generally OK; one unescaped flash

**Status:** Confirmed · **Severity:** Low

1. **Problem:** `{!! session('success') !!}` in testing view.
2. **Affected:** `resources/views/testing/index.blade.php`.
3. **Evidence:** Unescaped Blade.
4. **Impact:** XSS if flash ever contains user input.
5. **Fix:** Use `{{ }}`; audit `{!! !!}`.

---

## F12. Mobile layout good on new shell, inconsistent on legacy pages

**Status:** Confirmed · **Severity:** Low–Medium

1. **Problem:** Tailwind layout drawer is solid; SB Admin pages differ.
2. **Affected:** `layouts/app.blade.php` vs ~49 standalone pages.
3. **Impact:** Uneven mobile admin experience.
4. **Fix:** Migrate remaining pages to shared layout.

---

# 3. Detailed Remediation Plan

## Immediate Fixes (0–2 weeks)

| # | Change | Priority | Impact | Complexity | Dependencies | Risks | Type |
|---|--------|----------|--------|------------|--------------|-------|------|
| I1 | Fix API IDOR + Games authz | P0 | Stops PII/authz breach | M | Auth actor helpers | May break clients that relied on arbitrary `id` | Refactor API contract |
| I2 | Disable/remove `webhook.js` deploy | P0 | Removes remote deploy RCE vector | L | Ops access | Alt path users must switch to GH Actions | Delete / ops |
| I3 | Web login + forgot-password throttle | P0 | Cuts brute force | L | None | Legitimate multi-fail UX | Config/routes |
| I4 | Stop WhatsApp plaintext passwords | P0 | Credential hygiene | M | Email/reset UX | Users expect WA password | Redesign flow |
| I5 | Unique indexes + stop manual PersonID | P0 | Data integrity | M | Dedup data cleanup | Insert failures until duplicates cleaned | Migration |
| I6 | Liveform capacity atomicity + indexes on NewUsers/Limits | P0 | Correct enrolment under load | M | Schema migration | Lock contention tuning | Migration + refactor |
| I7 | Fix broken relative JS; pin/bundle Alpine/Tailwind plan kickoff | P0/P1 | Stops silent UI breakage | M | Vite | Visual regressions | Frontend fix |

### Suggested steps (I1 example)

1. Add `PersonAuthorization` helper: `accessiblePersonIds(actor)`, `assertCanView(actor, target)`.
2. Change `showPersons` to use authenticated id (or explicit admin scope).
3. Gate profile/calendar/export similarly.
4. Feature tests: user A cannot read user B.
5. Deploy behind mobile app release notes if contract changes.
6. Validate with Sanctum token integration tests + manual API calls.

### Safe deploy pattern

- Feature-flag new authz (`config/features.php`) if mobile apps need transition window.
- Deploy API restrictions first for write endpoints (Games), then read IDOR with monitoring.

---

## Short-Term Improvements (1–3 months)

| # | Change | Priority | Impact | Complexity |
|---|--------|----------|--------|------------|
| S1 | Pagination on all person/group/finance lists + API | P1 | Latency/memory | M |
| S2 | Index `GroupTable.IncludedUnderGroupID`; rewrite tree walk to single fetch | P1 | Admin page speed | M |
| S3 | Redis for cache/session/queue; first Jobs for FCM/WA/email | P1 | Reliability under spikes | M |
| S4 | Restore `phpunit.xml`; CI test job before deploy | P1 | Safety net | M |
| S5 | Deduplicate `routes/web.php`; document role matrix | P1 | Auth clarity | M |
| S6 | Extract person-search component; delete dead Blade demos | P1 | Dev velocity / XSS | L–M |
| S7 | Move Tailwind/Alpine to Vite build; safelist dynamic classes | P1 | Perf + supply-chain | M |
| S8 | Sentry + `/health` + daily log rotation; scrub PII logs | P1 | Operability | L–M |
| S9 | Gate Swagger in prod; API versioning `/api/v1` | P2 | API lifecycle | M |
| S10 | Split `PersonNewController` into Enrolment/Person/WaitingList controllers + services | P2 | Maintainability | H |

---

## Long-Term Architecture Improvements (3–12 months)

| # | Change | Priority | Impact | Complexity |
|---|--------|----------|--------|------------|
| L1 | Domain modules + service layer (Person, Enrolment, Finance, OrgTree, Notifications) | P2 | Team scale | H |
| L2 | Schema baseline + migration discipline; resolve PascalCase/snake_case collision | P2 | Env parity | H |
| L3 | Shared session/cache/files (Redis + object storage); optional 2nd app node | P2 | Horizontal scale | H |
| L4 | Closure table or materialized path for org tree | P2 | Tree query scale | H |
| L5 | Playwright critical journeys; expand Feature tests for finance | P2 | Regression safety | M–H |
| L6 | Staging environment + non-destructive deploys | P2 | Release safety | M |
| L7 | Policies/Gates replace string role middleware | P2 | Authz consistency | H |
| L8 | Gradually increase Eloquent models for hot entities (not big-bang ORM rewrite) | P3 | Consistency | H |

---

## Proposed target architecture

```
                    ┌──────────────┐
                    │  CDN / Vite  │  compiled CSS/JS
                    └──────┬───────┘
┌────────────┐      ┌──────▼───────┐      ┌─────────────┐
│ Mobile app │─────▶│ API v1       │─────▶│ Domain      │
└────────────┘      │ (Sanctum +   │      │ Services    │──▶ MySQL
┌────────────┐      │  Policies)   │      │ + Repos     │
│ Blade Admin│─────▶│ Web MVC      │─────▶│             │
└────────────┘      └──────┬───────┘      └──────┬──────┘
                           │ queue               │
                    ┌──────▼───────┐      ┌──────▼──────┐
                    │ Redis queue  │      │ Redis cache │
                    │ workers      │      │ sessions    │
                    └──────┬───────┘      └─────────────┘
                           ▼
                    FCM / Brevo / WhatsApp
```

**Why this fits:** Keeps Laravel + Blade (working admin UX), adds the missing async/cache/authz layers without rewriting the product as microservices.

---

## Recommended backend module structure

```
app/
  Domain/
    Person/
    Enrolment/
    OrgTree/
    Events/
    Finance/
    Inventory/
    Medicine/
    Notifications/
  Http/Controllers/   # thin
  Http/Controllers/Api/V1/
  Policies/
  Jobs/
  Support/            # Result types, authz helpers
```

Start by extracting **Enrolment** and **Person authorization** only — highest risk modules.

---

## Recommended frontend folder structure

```
resources/
  css/app.css                 # built Tailwind
  js/
    app.js
    alpine/
    modules/person-search.js
    modules/data-table.js
  views/
    layouts/app.blade.php
    components/               # x-* only shared UI
    person/
    enrolment/
    finance/
    _legacy/                  # quarantined SB Admin pages pending migration
```

---

## Suggested data-flow design

1. **Reads:** Controller → Query service (eager/join once) → DTO/array → Blade/JSON. Cache reference data.
2. **Writes:** Controller validates → Domain service opens DB transaction → emits domain event → queued side effects (notify).
3. **Authz:** Policy checks actor + resource before query returns PII.
4. **Enrolment:** Lock limit row → check unique national ID → insert → commit → queue welcome message.

---

## Scalability strategy

1. Vertical OK short-term; remove full-table lists and N+1 trees first.
2. Add Redis + queues before second app server.
3. Move uploads to object storage before multi-node.
4. DB: indexes + pagination + avoid full-table locks; later read replicas if reporting heavy.
5. Keep monolith until domain boundaries are clean — **no premature microservices**.

---

## Caching strategy

| Key | TTL | Invalidate |
|-----|-----|------------|
| `roles:person:{id}` | 5–15 min | On role change |
| `lookups:qetaa\|marhala\|blood` | 1–24 h | Admin CRUD |
| `orgtree:snapshot` | 5–30 min | Group mutations |
| `liveform:limits:{qetaa}:{sana}` | short | Limit updates |

Do not cache PII person profiles in shared Redis without encryption/access control.

---

## Database optimization strategy

1. **Immediate indexes:** `PersonInformation(RaqamQawmy)` UNIQUE, `ShamandoraCode` UNIQUE; same for NewUsers*; `MarhalaLiveFormLimit` PK; `GroupTable(IncludedUnderGroupID)`; `PersonGroup(GroupID)`.
2. Add PKs to tables currently without them (`NewUsersInformation`, satellite 1:1 tables) for InnoDB/ops health.
3. Pagination + covering indexes for list sorts (`ShamandoraCode`).
4. Preserve good patterns in Attendance/Finance/Medicine.
5. Use `EXPLAIN` on person list joins and tree queries in staging.

---

## Queue / background-job strategy

| Job | Trigger | Retry |
|-----|---------|-------|
| `SendFcmNotificationJob` | NotificationController | 3× backoff |
| `SendWhatsAppMessageJob` | Password/notify flows | 3× |
| `SendBrevoEmailJob` | Password reset | 3× |
| `ExportScoutsExcelJob` | Large exports | 1× + download link |

Supervisor: `queue:work redis --sleep=1 --tries=3`. Alert on `failed_jobs`.

---

## Observability strategy

- Sentry (PHP) for exceptions.
- Structured logs with `request_id`; never log full Request/passwords/RaqamQawmy.
- `/health`: DB ping, Redis ping, queue size.
- MySQL slow query log (>200ms).
- Uptime check on `/login` and `/api/login`.

---

## Security-improvement plan

1. IDOR + Games authz (P0).
2. Remove deploy webhook; rotate any secrets that lived in URLs/comments.
3. Throttle web auth endpoints; strengthen passwords; reset links not plaintext.
4. Policies + deny-by-default API.
5. Swagger auth-only in production.
6. Periodic `route:list` review for unauthenticated write routes (prior hole pattern).
7. Security regression tests for authz.

---

## Testing strategy

| Layer | Focus |
|-------|--------|
| Feature | Login throttle, IDOR negatives, liveform capacity race (parallel HTTP), booking unique, custody approve once |
| Unit | Org tree walker, ShamandoraCode generator, authz helpers |
| Browser | Login, person index pagination, liveform happy path |
| CI | phpunit must pass before deploy.yml SSH step |

---

## Phased migration roadmap

| Phase | Time | Outcome |
|-------|------|---------|
| **0 – Stabilize** | 2 weeks | Security P0s, indexes, webhook removal, throttles |
| **1 – Make it measurable** | 1 month | Sentry, health, phpunit+CI, pagination on hottest lists |
| **2 – Async & cache** | 1–2 months | Redis, jobs for notifications, Vite CSS/JS |
| **3 – Modularize** | 2–4 months | Extract Enrolment/Finance/OrgTree services; split god controllers |
| **4 – Scale-ready** | ongoing | Object storage, optional 2nd node, API v1, Policies |

**Do not** rewrite the monolith to microservices or replace Blade with React unless product requirements demand a SPA — evidence does not justify it.

---

## Prioritized summary table

| Priority | Area | Problem | Severity | Recommended Fix | Complexity | Expected Impact |
|----------|------|---------|----------|-----------------|------------|-----------------|
| P0 | API Authz | IDOR on persons/profile/calendar | Critical | Scope to auth user + org policies | M | Stops PII scrape |
| P0 | API Authz | Games writes unauthenticated by role | Critical | Enforce roles/policies | L | Stops privilege abuse |
| P0 | Data | Manual PersonID + migration races | Critical | Use AUTO_INCREMENT | M | Prevents ID collisions |
| P0 | Enrolment | Max-limit race + unindexed locks | Critical | Atomic limit + indexes | M | Correct capacity under load |
| P0 | Data | No UNIQUE on RaqamQawmy/ShamandoraCode | High | Unique indexes after dedup | M | Hard integrity guarantee |
| P0 | Deploy | Weak `webhook.js` secret | High | Remove; GH Actions only | L | Removes RCE vector |
| P0 | Security | Plaintext passwords via WhatsApp | High | Reset links + queue | M | Credential hygiene |
| P0 | Security | Web login unthrottled | High | throttle + lockout | L | Brute-force resistance |
| P0 | Frontend | Tailwind CDN + unpinned Alpine | Critical/High | Vite build + pin | M | Perf + supply-chain |
| P0 | Frontend | Broken relative JS paths | High | Absolute/Vite assets | L | Restores page JS |
| P1 | Perf | No pagination | High | paginate/cursor | M | Stable latency |
| P1 | Perf | Recursive group SQL, no index | High | Index + in-memory walk | M | Faster org pages |
| P1 | Arch | Sync FCM/email/WA | High | Redis queues + Jobs | M | Resilient notifications |
| P1 | Infra | File session/cache/local disk | High | Redis + object storage | M | Horizontal scale path |
| P1 | Schema | schema.sql vs migrations drift | High | Baseline + CI schema check | H | Reproducible envs |
| P1 | Quality | No real tests / CI deploy ungated | High | phpunit.xml + CI gate | M | Safer releases |
| P1 | Routes | Duplicate route/authz | Med–High | Deduplicate web.php | M | Predictable auth |
| P1 | Frontend | Duplicated person-search / XSS | High | Shared component | M | DRY + safer DOM |
| P1 | Frontend | Dead SB Admin views | High | Delete orphans | L | Less noise |
| P1 | Ops | No APM/health; PII logs | Med–High | Sentry + health + scrub | L–M | Faster incident response |
| P2 | Arch | God controllers / no services | High | Extract domain services | H | Team velocity |
| P2 | API | No versioning / `*` tokens | Medium | /v1 + scoped abilities | M | Safer evolution |
| P2 | Frontend | Monolithic blades / mixed JS | Medium | Split + conventions | H | Maintainability |
| P2 | Domain | Hardcoded Qetaa rules | Low–Med | Data-driven rules | M | Fewer deploys for org changes |
| P2 | Data | Manual person delete list | Medium | Cascades + service | M | Fewer orphans |
| P3 | ORM | 8 models / 96 tables | Medium | Gradual Eloquent for hot paths | H | Consistency over years |

---

## Assumptions & confidence notes

- **Confirmed** items were verified in source and/or `schema.sql` during this investigation.
- **Potential:** Whether workspace `.env` (`APP_DEBUG` duplicate values) matches production VPS; whether Swagger UI is publicly reachable in prod; exact live traffic volumes.
- Attendance AUTO_INCREMENT and PersonInformation AUTO_INCREMENT taken from `schema.sql` dump — treat as approximate snapshot of one environment.
- Medicine/Custody/Finance unique-constraint patterns are **working parts to preserve**, not rewrite.

---

## Investigation provenance

- Parallel explore agents (backend, database, frontend, infra/security) plus spot verification of `PersonApiController`, `MigrateNewEnrolments`, `AdminPasswordController`, `webhook.js`, `deploy.yml`, `layouts/app.blade.php`, and `schema.sql` DDL.
- Counts: 75 controllers / ~35k LOC, 8 models, 2 services, 0 jobs, ~251 views, 10 migrations, 2 example tests.
