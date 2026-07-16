# Eloquent-safe tables

## 2026-07-16 — Package 5 guidance

Prefer Eloquent **only** on tables with trustworthy PKs/uniques/FKs from Wave 2
migrations or newer first-class Laravel tables. Keep Query Builder for complex
reporting joins (finance CSV, attendance matrices, org-tree aggregates).

### Safe for Eloquent (models exist / preferred)

| Table | Model | Notes |
|-------|-------|-------|
| `PersonInformation` | `User` | Auth user. **Do not use** deprecated `Person`. |
| `PersonSystemPassword` | `Password` | Hidden `Password` attribute |
| `PersonImages` | `PersonImage` | |
| `PersonPhoneNumbers` | `PersonPhoneNumber` | PK = `PersonID` |
| `PersonRole` | `PersonRole` | |
| `PersonGroup` | `PersonGroup` | PK = `PersonGroupRoleID` |
| `PersonQetaa` | `PersonQetaa` | Composite-style; no surrogate |
| `PersonSanaMarhala` | `PersonSanaMarhala` | |
| `Roles` | `Roles` | PK hardened in Wave 2 |
| `GroupTable` | `Group` | |
| `Qetaa` | `Qetaa` | |
| `SanaMarhala` | `SanaMarhala` | |
| `NewUsersInformation` | `NewUserEnrolment` | Surrogate `id`, not `PersonID` |
| `MedicineInventory` | `MedicineInventory` | Laravel-created |
| `MedicineLocations` | `MedicineLocation` | |
| `MedicineStock` | `MedicineStock` | |
| `MedicineDispenseRecords` | `MedicineDispenseRecord` | |
| `MedicineStockLocks` | (optional) | Prefer QB until needed |
| WhatsApp campaign tables | `WhatsAppCampaign*` | |
| `audit_logs` | `AuditLog` | |
| `refresh_tokens` | `RefreshToken` | |
| `person_exam_mark` / `PersonExamMark` | `PersonExamMark` | |
| `games` | `Game` | |
| `Feedback` | `Feedback` | PascalCase survey table |

### Stay on Query Builder for now

- `SeasonEvent*`, `SeasonEventParticipantFinance*`, attendance matrices
- Custody / inventory issue complex joins
- Heavy `GROUP_CONCAT` / export reporting
- Legacy lookup tables without models (still OK via `LookupTableController` + QB)

### Rules

1. Never add `$guarded = []` on new models — use explicit `$fillable`.
2. Never map a second Eloquent model onto `PersonInformation` (`Person` is deprecated).
3. Extract Domain write paths to models when the table is in the safe list.
