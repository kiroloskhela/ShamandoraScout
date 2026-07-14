# Role / ability matrix (Wave 4 — Package F, phase 1)

Web `checkAuth` middleware is unchanged. This matrix covers API Gates/Policies only.

| Ability | Mechanism | Who may pass |
|---------|-----------|--------------|
| `games.view` | Gate | Any authenticated user |
| `games.create` | Gate | Any authenticated user |
| `games.update` | Gate | Any authenticated user |
| `games.delete` | Gate | Any authenticated user |
| `view` / `update` on `User` | `PersonPolicy` | Own `PersonID`, or `SuperAdmin` / `AdminQetaa` via `$user->role()` |

## Controllers wired

- `GamesApiController`: `authorize('games.*')` on index/show/store/update/destroy
- `PersonApiController`: `authorize('view', $user)` on profile/calendar (self-only IDOR still applies via `AuthenticatedPersonId`)

## Follow-ups (later phases)

- Bind `GamePolicy` to a real Game model when Games moves off `DB::table`
- Expand Person API so elevated roles can view others without IDOR override
