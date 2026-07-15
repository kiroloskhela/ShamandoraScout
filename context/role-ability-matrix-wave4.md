# Role / ability matrix (Wave 4–5 — Policies)

Web `checkAuth` middleware is unchanged. This matrix covers API Gates/Policies.

| Ability | Mechanism | Who may pass |
|---------|-----------|--------------|
| `viewAny` / `create` on `Game` | `GamePolicy` | Any authenticated user |
| `view` / `update` / `delete` on `Game` | `GamePolicy` | Any authenticated user |
| `games.*` Gates | Delegate to `GamePolicy` / auth | Any authenticated user |
| `view` / `update` on `User` | `PersonPolicy` | Own `PersonID`, or `SuperAdmin` / `AdminQetaa` |

## Controllers wired

- `GamesApiController`: Eloquent `Game` + `$this->authorize(...)` on CRUD
- `PersonApiController` profile/calendar: authorize **target** `User` from route `{id}` (self or elevated). Non-elevated requesting another id → **403** (no silent remapping to self)

## Done in phase 2

- Eloquent `App\Models\Game` (`Games` / `GameID`)
- `GamePolicy` registered in `AuthServiceProvider`
- Person API elevated view without opening IDOR to everyone
