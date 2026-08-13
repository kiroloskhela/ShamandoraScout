# Wave 5 plan (revised after correctness + security review)

**Goal:** Served people can log in as Mkhdom with own-only app access; staff keep today’s app; lost refresh responses no longer force password login.

**Locked after review**

- **No refresh rotation** (echo the same refresh token). Do **not** claim this detects theft. Compensating: 30-day TTL; presenting a **revoked** refresh of a known family kills that family (leftover access PATs); password change / last app-role removed / SA strip revoke **all** families + all PATs.
- `family_id` **nullable** + indexed; first refresh after deploy lazy-assigns a UUID. `down()` only drops the column.
- Refresh is `DB::transaction` + `lockForUpdate` on the hash row (lookup **includes** revoked rows). Soft-revoke only.
- Refresh **inserts** a new 1h PAT; **does not delete** prior family PATs (avoids lost-access-token races).
- Logout: if access PAT name is `family:{id}`, revoke that family (refresh + that user’s PATs with that name). Else (legacy `api-token`): revoke all of that user’s refresh rows + current PAT (today’s behavior).
- Family PAT delete is always `(tokenable_id, name)` — never name-only.
- `permissions[]` uses the **same source as `userCan`** (seed map when enforce is off). Flutter: omit/`null` → role_names fallback; `[]` → no keys, do not invent staff access.
- `/attendance/mine` = `Attendance.ServedID = Auth PersonID`. Paginated. Ignore client `id`.
- `/me` PII allowlist only (`person_id`, display name, `role_names`, `permissions`). No national ID / phones dump.
- PersonRole: index lists **all** assignments. Non-قادة people may receive **Mkhdom only**. Staff roles still require قادة. SuperAdmin stays behind existing SuperAdminGuard.
- Login issues tokens only if SuperAdmin **or** the user has at least one `api.*` / `mobile.*` key. Otherwise 401 same body as bad password (no user enumeration).
- Insert `Roles.Mkhdom` **before** permission seed. No Mkhdom **users** in seeders.
- Website API ships before the Flutter release that enforces permissions.

**Branches / PRs:** `feat/authz-wave-5`. Website includes waves 0–5. Mobile is Wave 5 only. `kiroloskhela` / `kirokhela`.
