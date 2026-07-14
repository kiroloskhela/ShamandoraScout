# Database Schema Reality Check (schema.sql)

## 2026-07-14 — Confirmed before any remediation steps

**Source of truth:** `schema.sql` (96 tables).  
**Not source of truth:** Laravel migrations (10 files) or Eloquent models (8 files).

The app runs on a **legacy PascalCase MySQL schema** with a thin Laravel layer on top. Most controllers use `DB::table()`, so missing models do not break runtime today — but the **schema itself** has structural gaps that cause races, duplicates, slow trees, and make proper Eloquent models hard/unsafe to add until the DB is fixed.

---

## Model coverage vs real tables

| Reality | Count |
|---------|-------|
| Tables in `schema.sql` | **96** |
| Eloquent models in `app/Models` | **8** |
| Tables with usable model mapping | ~6–7 (several models are wrong/fragile) |

### Existing models — what they actually map to

| Model | Maps to | Schema issues for this model |
|-------|---------|------------------------------|
| `User` | `PersonInformation` | OK as auth user; no UNIQUE on `RaqamQawmy`/`ShamandoraCode`; password is **not** on this table |
| `Person` | `PersonInformation` | Duplicate of `User` on same table; `roles()` relation is **broken** (`hasOne` with 4 args) |
| `Password` | `PersonSystemPassword` | Table OK (PK=`PersonID`) |
| `PersonImage` | `PersonImages` | PK=`PersonID` but **no FK** to `PersonInformation` in schema |
| `Roles` | `Roles` | Model sets `primaryKey=RoleID` but table has **NO PRIMARY KEY** |
| `Rotab` | `RotbaInformation` | Exists; `$guarded = []` / fillable mismatch (`rotba_id` vs `RotbaID`) |
| `Feedback` | `Feedback` | Real survey table; also empty stub table `feedback` (collision risk) |
| `RefreshToken` | `refresh_tokens` | Newer snake_case table — OK |

### Dead / misleading tables

- **`users`** — stock Laravel table; **not used** for auth (`User` model → `PersonInformation`).
- **`feedback`** (snake_case, empty stub) vs **`Feedback`** (real data) — dangerous on case-insensitive MySQL.
- **`password_resets`** + **`password_reset_tokens`** — duplicate reset mechanisms.

### Critical domain tables with **zero** models

`GroupTable`, `PersonGroup`, `PersonRole`, `PersonQetaa`, `PersonSanaMarhala`, `Qetaa`, `SanaMarhala`, `NewUsersInformation`, `NewUsersInformationWaitinglist`, `MarhalaLiveFormLimit`, `Attendance`, `SeasonEvent*`, `CustodyRequests`, `Medicine*`, etc.

**Conclusion:** Adding models *before* fixing PKs/uniques/FKs will bake broken assumptions into Eloquent (e.g. `Roles` already claims a PK the table does not have).

---

## Schema problems that need DB edits (ranked)

### Tier 0 — Must fix before safe concurrency / identity

#### 1. `PersonInformation` — missing uniqueness on identity columns
```sql
-- Current: PRIMARY KEY (PersonID) ONLY
-- Missing:
UNIQUE (RaqamQawmy)
UNIQUE (ShamandoraCode)
-- Also missing index/FK on BloodTypeID
```
App treats these as unique; DB does not enforce. Guests/FamilyMembers already have `UNIQUE(RaqamQawmy)` — core persons do not.

#### 2. `NewUsersInformation` + `NewUsersInformationWaitinglist` — no PK, no indexes, no FKs
- 51 columns, denormalized enrolment staging tables.
- Manual `PersonID` inserts in app code.
- `lockForUpdate()` on `RaqamQawmy` = full table scan lock.
- Needed at minimum:
  - PRIMARY KEY (prefer AUTO_INCREMENT surrogate, or `PersonID` if kept)
  - UNIQUE (`RaqamQawmy`)
  - INDEX (`QetaaID`, `SanaMarhalaID`) for capacity counts
  - INDEX (`IsApproved`) if filtered often

#### 3. `MarhalaLiveFormLimit` — no PK, no unique, no indexes
```sql
-- Current bare table:
QetaaID, SanaMarhalaID, MaxLimit, Year
```
Capacity enforcement cannot be locked safely. Needed:
```sql
PRIMARY KEY (QetaaID, SanaMarhalaID, Year)  -- or unique equivalent
-- + FKs to Qetaa, SanaMarhala when those PKs are solid
```

### Tier 1 — Must fix before useful Eloquent / org queries

#### 4. Lookup / auth tables with **no PRIMARY KEY**
Includes: `Roles`, `BloodType`, `Districts`, `Faculty`, `University`, `GroupRole`, `Jobs`, `QuestionsTypes`, …

Especially **`Roles`**: app auth joins on `RoleID` but DB has no PK/unique → duplicate RoleIDs possible; Eloquent `Roles` model is incorrect today.

#### 5. Person satellite / junction tables without PK or incomplete keys

| Table | Has | Missing |
|-------|-----|---------|
| `PersonQetaa` | KEY(PersonID), FK Person | PK; UNIQUE(PersonID) or (PersonID,QetaaID); KEY+FK QetaaID |
| `PersonSanaMarhala` | KEY(PersonID), FK Person | PK; UNIQUE; KEY+FK SanaMarhalaID |
| `PersonRole` | KEY(PersonID), FK Person; has `PersonRoleID` col but **not PK** | PRIMARY KEY(PersonRoleID) or UNIQUE(PersonID,RoleID); KEY+FK RoleID; make RoleID referenceable |
| `PersonGroup` | PK AI, KEY+FK Person | KEY+FK GroupID; KEY+FK GroupRoleID; UNIQUE(PersonID,GroupID,GroupRoleID)? |
| `PersonalPhysicalAddress` | KEY+FK Person | PK (PersonID) if 1:1; FKs to Manteqa/District |
| `PersonJob`, `PersonLearningInformation`, `PersonSpiritualFatherInformation`, `PersonEgazetBetakatTaqaddom`, `PersonRotbaKashfeyya` | KEY+FK Person only | PK; decide 1:1 → PK(PersonID) |
| `PersonEntryQuestions` / NewUsers question tables | nothing | PK; FKs |
| `PersonImages` | PK(PersonID) | **FK to PersonInformation** |

#### 6. `GroupTable` — tree parent unindexed, no FKs
```sql
GroupID PK, GroupTypeID, IncludedUnderGroupID, GroupName
-- Missing: KEY(IncludedUnderGroupID), KEY(GroupTypeID), self-FK, FK GroupType
```
Every recursive tree walk full-scans.

#### 7. `GroupQetaa` — PK only, no UNIQUE(GroupID,QetaaID), no FKs
Used heavily in API person listing joins.

### Tier 2 — Consistency / cleanup (still important)

#### 8. `Feedback` vs `feedback` collision
Drop unused `feedback` stub after confirming empty/unused.

#### 9. Vestigial `users` table
Document as unused or drop after confirming nothing references it.

#### 10. Missing FKs on many “ID” columns
Examples: `PersonInformation.BloodTypeID` → `BloodType` (but BloodType needs PK first); `PersonGroup.GroupID` → `GroupTable`; `PersonRole.RoleID` → `Roles`.

#### 11. Naming dualism
~85% PascalCase legacy + ~15% snake_case Laravel (`refresh_tokens`, `medicine_*` style mixed — medicine tables actually keep PascalCase columns). Do **not** mass-rename now; enforce “new tables match domain convention” and fix collisions only.

### Tier 3 — Already in good shape (do not “fix”)

Preserve patterns from:
- `Attendance` (unique + indexes)
- `SeasonEventParticipantFinance*` (uniques, FKs, checks)
- `SeasonEventWaitingList`
- `MedicineInventory` / `MedicineStock` / locks
- `CustodyRequests` (app-level atomic status update)
- `FamilyMembers` / `Guests` UNIQUE `RaqamQawmy`

Use these as templates when hardening older tables.

---

## Why DB edits should come before models

1. **Eloquent requires reliable PKs** — `Roles`, `NewUsersInformation`, junctions currently violate that.
2. **Relationships need FKs + indexes** — without them, `belongsTo`/`hasMany` will be slow and unsafe.
3. **Uniques belong in DB** — app-level `exists()` checks are racy (liveform / migration).
4. **AUTO_INCREMENT should own IDs** — stop app `MAX+1` once PKs are correct; staging tables need a clear ID strategy.

Recommended order:
1. Data cleanup (duplicate RaqamQawmy / ShamandoraCode / RoleID)
2. Add PKs / UNIQUEs / indexes (online-safe where possible)
3. Add FKs (after orphans cleaned)
4. Then add Eloquent models + relationships matching the hardened schema
5. Only then refactor controllers off raw SQL gradually

---

## Proposed DB edit packages (for when we start — not started yet)

### Package A — Identity & enrolment (highest business risk)
- Unique indexes on `PersonInformation(RaqamQawmy)`, `(ShamandoraCode)`
- PK + unique RaqamQawmy + capacity index on `NewUsersInformation*`
- PK/unique on `MarhalaLiveFormLimit`
- Align app to stop manual PersonID on `PersonInformation` inserts

### Package B — Auth & roles
- PRIMARY KEY on `Roles(RoleID)`
- PRIMARY KEY / UNIQUE on `PersonRole`
- FK `PersonRole.RoleID` → `Roles.RoleID`
- INDEX `PersonRole.RoleID`

### Package C — Org tree & membership
- INDEX `GroupTable(IncludedUnderGroupID)`, `(GroupTypeID)`
- INDEX+FK `PersonGroup(GroupID)`, `(GroupRoleID)`
- PK/UNIQUE + FK on `PersonQetaa`, `PersonSanaMarhala`
- UNIQUE+FK on `GroupQetaa(GroupID,QetaaID)`

### Package D — Person 1:1 satellites
- PK=`PersonID` on address/job/learning/spiritual/images where 1:1
- FK `PersonImages` → `PersonInformation`
- FK `BloodType` PK then `PersonInformation.BloodTypeID`

### Package E — Housekeeping
- Drop or rename stub `feedback`
- Document/drop unused `users`
- Migration files that match these changes (so schema.sql is not the only truth going forward)

---

## Assumptions

- Production DB matches this `schema.sql` dump closely (AUTO_INCREMENT hints: PersonInformation≈1688).
- Some NO_PK lookup tables may already have unique RoleIDs in practice — still must be enforced before FKs.
- Dedup queries must run **before** UNIQUE indexes or migration will fail.

## Status

**Investigation only — no migrations or model changes applied yet.**  
Awaiting decision on which package (A–E) to implement first.
