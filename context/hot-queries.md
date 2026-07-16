# Hot Queries

## 2026-07-16 — Package 4 database consistency and perf hygiene

Watch these paths when profiling production or a local MySQL-sized dataset:

- `PersonDirectoryController` / `PersonSearchService` directory search: name and identity searches should continue to use the centralized `LikeSearch` helper and avoid widening result columns beyond what the index page needs.
- `SeasonEventBookingFinanceController@index`: the booking finance index uses a grouped, paginated query with payment subqueries. Avoid adding columns to the select/group list unless they are rendered; if this becomes slow, profile the paginator count query separately from the page query.
- `QetaaTreeController` / `GroupTreeService`: org tree reads depend on `GroupTable`, `PersonGroup`, `GroupQetaa`, and `PersonQetaa` joins. Package C indexes are the expected baseline before further tree/controller refactors.

Concrete improvement in this package: `ShareAuthRoleFlags` loads authenticated role names once per web request and shares `$authRoles`, `$authRoleSet`, and sidebar booleans with views. This removes the repeated `role()->where(...)->exists()` checks from `layouts.app`.

No new query-index migration was added in Package 4. The current hot paths already depend on Wave 2 Packages A-E schema hardening, and adding another migration without local MySQL verification would be riskier than documenting and profiling first.

MySQL CI is deferred. A future optional workflow should run against a MySQL service explicitly and not alter the existing deploy workflow or require production secrets.
