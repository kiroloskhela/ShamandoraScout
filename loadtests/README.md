# ShamandoraScout load tests (k6)

Read-only HTTP load tests against the real Laravel routes. Bare `k6 run loadtests/k6.js` is **smoke** (1 VU, 30s). `SCENARIO=baseline` is the 50-VU staff mix plus a 50 req/min API probe — kept under Laravel’s **60 req/min per user** limiter so 429s are not mistaken for a server crash.

Install: `brew install k6` ([k6 docs](https://grafana.com/docs/k6/latest/set-up/install-k6/)).

## 1. Critical flows (what this hits)

This is an ops platform, not a storefront. “Checkout” here is **season-event booking finance**; “search” is **person directory / typeahead**.

| Flow | Why it is heavy | Endpoints in the script |
|------|-----------------|-------------------------|
| Public enrolment | Seasonal spike; `EnsureLiveFormIsOpen` hits `AppSettings` + DB | `GET /liveform` (200 or 503 if closed), `GET /login-auth`, `GET /health`, `GET /img/shamandora.webp` |
| Staff home | Joins person/group/event tables | `GET /` |
| Person directory + search | Multi-table LIKE search (`PersonSearchService`) | `GET /person`, `GET /person?q=ahmed` (Khadem). SuperAdmin also: `GET /person/search?q=ahmed` |
| All-persons directory | SuperAdmin-only; full-org paginated list | `GET /person/ShowPersons` |
| Org tree | Group hierarchy + people overview | `GET /team/structure` |
| Attendance | Roster + live snapshot | `GET /attendance/manage`, `GET /attendance/live`, `GET /attendance/live/snapshot` |
| Event finance | Booking index/selector (closest thing to checkout) | `GET /finance`, `GET /event-booking-finance/selector` |
| Enrolment admin + export form | Lists + Excel form (GET only — **no download POST**) | `GET /new-enrolments`, `GET /export/served-people` |
| File-upload surface | Secretary documents live on local disk | `GET /secretary` only — **no `POST /secretary/upload`** |
| Mobile API (hottest) | Unpaginated multi-join roster | `GET /api/show-persons` |
| Mobile API (rest) | Profile joins, calendar, attendance roster, tree | `GET /api/me`, `/api/person/{id}`, `/api/calendar/{id}`, `/api/attendance/events`, `/api/attendance/persons/{seasonEventId}`, `/api/team-structure`, `/api/custody/requests`, `/api/curricula` |

**Never in the default mix (mutating or throttled):** `POST /liveform/step1`, `POST /api/attendance/save`, finance store/refund, secretary upload, `GET /api/attendance/mine` (`throttle:30,1`), login inside the VU loop, `/health?token=`.

403 on role-gated pages is expected (Khadem vs SuperAdmin) and is **not** a failure. 5xx, timeouts, and “still on the login form” are failures.

## 2. Run

```bash
brew install k6
cp loadtests/k6.env.example loadtests/k6.env   # fill PERSON_ID + PASSWORD
set -a && source loadtests/k6.env && set +a

# Always this first — 1 VU, 30s, proves login + CSRF + cookies
k6 run -e SCENARIO=smoke loadtests/k6.js

# Steady staff load
k6 run -e SCENARIO=baseline loadtests/k6.js

# Find the breaking point (0 → 500 VUs / 5 min, then 2 min hold)
k6 run -e SCENARIO=ramp loadtests/k6.js

# Viral / enrolment-season surge (public only, no login)
k6 run -e SCENARIO=public loadtests/k6.js

# Sudden 10 → 1000 VUs in 10s (do not use on production without ALLOW_SPIKE_PROD)
k6 run -e SCENARIO=spike loadtests/k6.js

# Memory leak / slow degradation (30 VUs, 90 min; session lifetime is 120 min)
k6 run -e SCENARIO=soak loadtests/k6.js
k6 run -e SCENARIO=soak -e SOAK_DURATION=60m loadtests/k6.js
```

Production (`https://shamandorascout.com`) is **fail-closed**:

```bash
k6 run -e SCENARIO=smoke -e BASE_URL=https://shamandorascout.com \
  -e ALLOW_PROD=1 loadtests/k6.js

# ramp or spike against prod also need:
k6 run -e SCENARIO=ramp -e BASE_URL=https://shamandorascout.com \
  -e ALLOW_PROD=1 -e ALLOW_SPIKE_PROD=1 loadtests/k6.js
```

If liveform is open, `public` / `ramp` / `spike` against production also need `ALLOW_LIVEFORM_LOAD=1`. Do not run those during an enrolment window. Do not pass `--http-debug` (it prints tokens and person payloads).

Use a **dedicated Khadem** (or similar `api.mobile.staff`) account scoped to one sector — not SuperAdmin and not someone’s daily login. SuperAdmin makes `/person/ShowPersons` and finance return 200, which is a heavier (and more PII-heavy) test; only do that on staging.

Better staff concurrency (avoids one file-session lock):

```bash
k6 run -e SCENARIO=baseline -e WEB_SESSIONS=5 loadtests/k6.js
```

Setup staggers web logins by 16s so it stays under `throttle:5,1` on `POST /login`.

## 3. Metrics — what “good” looks like

Watch k6’s end-of-run summary **and** the VPS while the test runs.

| Metric | Where | Good | Flag |
|--------|--------|------|------|
| **p50 / p95 / p99** `http_req_duration` | k6 | p50 &lt; 300ms static/health; p95 &lt; 1s for HTML/API reads; p99 &lt; 2s | **p95 &gt; 1s** on baseline/soak (script threshold). Ramp/spike allow p95 &lt; 3s while you find the cliff. |
| **Error rate** `unexpected_fail` | k6 | ~0% until past capacity | Baseline/soak fail the run if &gt;1%. 403/429/liveform-503 are counted separately (`authz_denied`, `api_throttled`, `liveform_closed`). |
| **HTTP 5xx / timeouts** | k6 + nginx | None on baseline | First sign of php-fpm exhaustion or MySQL wait timeout |
| **RPS** `http_reqs` | k6 | Whatever p95 still holds | Capacity = highest VU count where p95 &lt; 1s **and** unexpected_fail &lt; 1% |
| **Degrade VU count** | ramp graph | — | First stage where p95 crosses 1s |
| **Fail VU count** | ramp/spike | — | First stage with 5xx, resets, or p99 &gt; 8s |
| **CPU** | `top` / `htop` on VPS | php-fpm + mysqld &lt; ~70% each at baseline | 100% php-fpm with waiting queue = worker cap |
| **Memory** | `free -h`; soak over 90m | Flat RSS for `php-fpm` | Steady climb across soak = leak or unbounded result sets |
| **php-fpm busy** | `sudo service php8.3-fpm status`; `pm.max_children` | Spare workers | `max children reached` in `/var/log/php8.3-fpm.log` |
| **MySQL threads / pool** | `SHOW STATUS LIKE 'Threads_connected'; SHOW PROCESSLIST;` | Connected ≪ `max_connections`; no pile of `Locked` | Threads_connected stuck at max; `too many connections` |
| **Session files** | `ls storage/framework/sessions \| wc -l` | Stable | Explosion of files, or one file under heavy lock (shared cookie) |

Custom k6 counters:

- `api_throttled` — Laravel `ThrottleRequests:api` (60/min per user or IP). Public `GET /api/version/check` is on that limiter; many VUs from one IP will 429. That is counted here, not as `unexpected_fail`.
- `liveform_closed` — enrolment is off (`503`). Public mix still valid; that path is cheap.
- `authz_denied` — 403. High is normal for a Khadem hitting SuperAdmin routes.

### While the test runs (SSH to the VPS)

```bash
# workers + CPU
sudo systemctl status php8.3-fpm nginx mysql laravel-queue.service --no-pager
watch -n2 'ps -o pid,pcpu,pmem,rss,cmd -C php-fpm8.3 -C mysqld -C nginx'

# php-fpm pool (adjust path if the pool file differs)
grep -E 'pm\.|listen' /etc/php/8.3/fpm/pool.d/www.conf

# DB
mysqladmin status processlist
# or: mysql -e "SHOW GLOBAL STATUS LIKE 'Threads_connected'; SHOW GLOBAL STATUS LIKE 'Max_used_connections';"

# nginx
sudo tail -f /var/log/nginx/access.log /var/log/nginx/error.log
# look for 499/502/504 and upstream timed out

# Laravel
tail -f /var/www/shamandora/storage/logs/laravel-$(date +%Y-%m-%d).log
```

There is **no auto-scaling**. This is one VPS (`php8.3-fpm` + nginx + MySQL). Redis is optional and **not** the default (`CACHE_DRIVER=file`, `SESSION_DRIVER=file`). Do not expect `redis-cli` to show load unless you already switched drivers.

## 4. Bottlenecks specific to this stack

Confirmed from the current code and deploy path (single VPS, Laravel 10, MySQL).

**API rate limit is the first hard ceiling.** `RouteServiceProvider` applies `Limit::perMinute(60)->by(user id \| IP)` on the entire `api` middleware group. One Sanctum token (or many tokens for the **same** PersonID) cannot exceed 60 req/min. Extra VUs will 429. That is working as designed. To measure PHP/MySQL API capacity beyond that, raise the limiter on a **staging** clone, or use **distinct PersonIDs**.

**`GET /api/show-persons` is unpaginated.** `PersonApiQueryService::personsVisibleTo()` `->get()`s the full visible roster with a wide join (entry questions, sana, qetaa, phones, images, groups). Under concurrency this is the first API call that will burn CPU and RAM. Web `/person` is paginated (25); the mobile list is not.

**PHP-FPM is multi-process, not single-thread Node.** Each request occupies one worker until it returns. Slow SQL or a big JSON encode holds a worker. When `pm.max_children` is exhausted, nginx queues then 502/504. There is no request-level concurrency inside one worker.

**File sessions serialize a shared cookie.** `SESSION_DRIVER=file` locks `storage/framework/sessions/{id}` for the request. 50 VUs with `WEB_SESSIONS=1` contend on **one** file and will look worse than 50 real people. Use `WEB_SESSIONS=5` (or more, 15s apart) for staff tests. Public spike (`SCENARIO=public`) avoids this entirely.

**Cache is almost unused.** Default `CACHE_DRIVER=file`. `Cache::` appears for `AppSettings` and permission keys only. Person lists, trees, and finance indexes hit MySQL every time. No Redis/CDN in front of HTML. Static files under `public/` (`/img/shamandora.webp`) should be served by **nginx directly**; if k6 shows PHP timings on that URL, nginx is mis-rooted.

**Queue does not absorb HTTP.** `QUEUE_CONNECTION=database` with a systemd worker. The load mix does not enqueue work. A real attendance-QR / WhatsApp burst would add DB-queue rows and a single worker — a different bottleneck than these GETs.

**Liveform writes are racy under a real enrolment spike.** The script does **not** POST `/liveform/step1` (that would create people). Capacity checks still have TOCTOU history; a GET-only test will **not** prove enrolment correctness. Treat a viral liveform moment as: public GETs (this script) + a separate, staging-only write test with disposable national IDs.

**No CDN / no horizontal scale.** Deploy is GitHub Actions → one host `/var/www/shamandora`. Vite-built CSS/JS should already be hashed files from nginx; leftover `public/vendor` (Font Awesome, etc.) is large if any page still loads it.

**N+1 / fat reads.** Org tree and person profile still join many satellite tables in one query (better than per-row queries, but large). Attendance `~98k` AUTO_INCREMENT in the old dump: roster endpoints grow with season size. Exports (`POST /export/served-people`) are excluded because they can lock/CPU-spike; run one by hand after the GET mix.

**DB connections.** PHP PDO does not pool like a Java app. Each FPM worker holds its own MySQL connection. Capacity ≈ `min(pm.max_children, MySQL max_connections)`. If `max_connections` is 150 and FPM is 20, FPM is the cap; if FPM is 80 and MySQL is 50, MySQL is the cap.

## 5. After you run it

Paste back:

1. The k6 **end-of-run summary** (checks, `http_req_duration` percentiles, `http_reqs`, `unexpected_fail`, custom counters).
2. Which `SCENARIO` + `BASE_URL` + `WEB_SESSIONS`.
3. VPS notes: php-fpm `max_children` / “max children reached”, MySQL `Threads_connected` / `Max_used_connections`, CPU%, whether RSS climbed during soak.

Then we can name: **safe concurrent-user capacity now (highest VU with p95 &lt; 1s and &lt;1% unexpected errors), **the first thing that breaks** (limiter vs FPM vs MySQL vs session files vs `show-persons`), and the **highest-impact fix**.
