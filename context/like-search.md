# Shared search (`LikeSearch`)

Controllers must not invent their own LIKE algorithms. Use:

| Helper | Role |
|--------|------|
| `App\Support\LikeSearch` | Normalize term (`q`/`search`), escape wildcards, OR / word-name clauses, raw SQL fragments |
| `App\Domain\Person\PersonSearchService` | Person directory pagination + common typeaheads |

## Usage

```php
$term = LikeSearch::fromRequest($request);           // q or search
$term = LikeSearch::fromRequest($request, ['search', 'q'], 2); // typeahead min length

LikeSearch::applyOr($query, $term, ['pi.FirstName', 'pi.ShamandoraCode'], [
    "CONCAT_WS(' ', pi.FirstName, pi.SecondName, pi.ThirdName, pi.FourthName)",
]);

$fragment = LikeSearch::sqlOr(LikeSearch::personDirectoryColumns(), $term);
// $fragment['sql'], $fragment['bindings'] → append to SqlPaginator SQL
```

Presets: `personDirectoryColumns()`, `allowedPeopleColumns()`, `personIdentityFields()`, `namedPartyFields()`.

Domain scoping (auth, qetaa, event eligibility) stays in the controller/service — only the text match belongs in `LikeSearch`.
