<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;

/**
 * Shared LIKE search helpers for raw SQL and Query Builder.
 *
 * Controllers keep domain scoping (joins, whereIn, auth); this class only
 * builds the text-match clauses so every screen does not invent its own algo.
 */
class LikeSearch
{
    /**
     * Normalize a free-text term. Returns null when empty or below min length.
     */
    public static function term(?string $input, int $minLength = 0): ?string
    {
        $term = trim((string) $input);
        if ($term === '') {
            return null;
        }
        if ($minLength > 0 && mb_strlen($term) < $minLength) {
            return null;
        }
        if (mb_strlen($term) > 100) {
            $term = mb_substr($term, 0, 100);
        }

        return $term;
    }

    /**
     * Read the first non-empty search param from a request (q / search / …).
     */
    public static function fromRequest(Request $request, array $keys = ['q', 'search'], int $minLength = 0): ?string
    {
        foreach ($keys as $key) {
            if ($request->filled($key)) {
                $term = self::term((string) $request->input($key), $minLength);
                if ($term !== null) {
                    return $term;
                }
            }
        }

        return null;
    }

    /**
     * Escape LIKE wildcards and wrap with %.
     */
    public static function wildcard(string $term): string
    {
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term);

        return '%'.$escaped.'%';
    }

    /**
     * Digits only from a term (for phone matching). Null when fewer than 3 digits.
     */
    public static function digits(?string $term, int $minLength = 3): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $term) ?? '';
        if ($digits === '' || strlen($digits) < $minLength) {
            return null;
        }

        return $digits;
    }

    /**
     * Common EG phone string variants for LIKE matching (portable; no REGEXP_REPLACE).
     *
     * @return list<string>
     */
    public static function phoneDigitVariants(string $digits): array
    {
        $variants = [$digits];
        if (strlen($digits) >= 10) {
            $last10 = substr($digits, -10);
            $variants[] = $last10;
            $variants[] = '0'.$last10;
            $variants[] = '20'.$last10;
            $variants[] = '+20'.$last10;
        }

        return array_values(array_unique($variants));
    }

    /**
     * Split into non-empty words (Arabic/Latin whitespace).
     *
     * @return list<string>
     */
    public static function words(string $term): array
    {
        $parts = preg_split('/\s+/u', trim($term)) ?: [];

        return array_values(array_filter($parts, fn ($w) => $w !== ''));
    }

    /**
     * OR across columns: (col1 LIKE ? OR col2 LIKE ? …).
     *
     * @param  EloquentBuilder|QueryBuilder  $query
     * @param  list<string>  $columns  Qualified column names or raw expressions (use whereRaw for expressions)
     * @param  list<string>  $rawExpressions  Optional SQL expressions matched with whereRaw (e.g. CONCAT_WS(...))
     */
    public static function applyOr($query, string $term, array $columns = [], array $rawExpressions = []): void
    {
        $like = self::wildcard($term);

        $query->where(function ($sub) use ($like, $columns, $rawExpressions) {
            $first = true;
            foreach ($columns as $column) {
                if ($first) {
                    $sub->where($column, 'like', $like);
                    $first = false;
                } else {
                    $sub->orWhere($column, 'like', $like);
                }
            }
            foreach ($rawExpressions as $expression) {
                if ($first) {
                    $sub->whereRaw($expression.' LIKE ?', [$like]);
                    $first = false;
                } else {
                    $sub->orWhereRaw($expression.' LIKE ?', [$like]);
                }
            }
        });
    }

    /**
     * applyOr plus phone digit-variant OR matches when the term looks numeric.
     *
     * @param  EloquentBuilder|QueryBuilder  $query
     * @param  list<string>  $columns
     * @param  list<string>  $rawExpressions
     * @param  list<string>  $phoneColumns
     */
    public static function applyFlexibleOr(
        $query,
        string $term,
        array $columns = [],
        array $rawExpressions = [],
        array $phoneColumns = [],
    ): void {
        $query->where(function ($sub) use ($term, $columns, $rawExpressions, $phoneColumns) {
            self::applyOr($sub, $term, $columns, $rawExpressions);

            $digits = self::digits($term);
            if ($digits === null || $phoneColumns === []) {
                return;
            }

            foreach (self::phoneDigitVariants($digits) as $variant) {
                $like = self::wildcard($variant);
                foreach ($phoneColumns as $column) {
                    $sub->orWhere($column, 'like', $like);
                }
            }
        });
    }

    /**
     * Word-mode names OR full CONCAT OR identity/phone columns (flexible person match).
     *
     * @param  EloquentBuilder|QueryBuilder  $query
     */
    public static function applyFlexiblePersonMatch(
        $query,
        string $term,
        string $pi = 'pi',
        ?string $ppn = 'ppn',
    ): void {
        $nameColumns = [
            "{$pi}.FirstName",
            "{$pi}.SecondName",
            "{$pi}.ThirdName",
            "{$pi}.FourthName",
        ];
        $fields = self::personIdentityFields($pi, $ppn);
        $phoneColumns = $ppn !== null ? self::personPhoneColumns($ppn) : [];

        $query->where(function ($outer) use ($term, $nameColumns, $fields, $phoneColumns) {
            $outer->where(function ($names) use ($term, $nameColumns) {
                self::applyWordNames($names, $term, $nameColumns);
            });

            $outer->orWhere(function ($identity) use ($term, $fields, $phoneColumns) {
                self::applyFlexibleOr(
                    $identity,
                    $term,
                    $fields['columns'],
                    $fields['raw'],
                    $phoneColumns,
                );
            });
        });
    }

    /**
     * Word mode for names: each word must match at least one of the name columns (AND of ORs).
     *
     * @param  EloquentBuilder|QueryBuilder  $query
     * @param  list<string>  $nameColumns
     */
    public static function applyWordNames($query, string $term, array $nameColumns): void
    {
        $words = self::words($term);
        if ($words === []) {
            return;
        }

        $query->where(function ($outer) use ($words, $nameColumns) {
            foreach ($words as $word) {
                $like = self::wildcard($word);
                $outer->where(function ($inner) use ($like, $nameColumns) {
                    $first = true;
                    foreach ($nameColumns as $column) {
                        if ($first) {
                            $inner->where($column, 'like', $like);
                            $first = false;
                        } else {
                            $inner->orWhere($column, 'like', $like);
                        }
                    }
                });
            }
        });
    }

    /**
     * Raw SQL OR fragment + bindings for SqlPaginator lists.
     *
     * @param  list<string>  $columns  SQL column expressions (may include CAST(...))
     * @return array{sql: string, bindings: list<string>}
     */
    public static function sqlOr(array $columns, string $term): array
    {
        $like = self::wildcard($term);
        $parts = [];
        $bindings = [];
        foreach ($columns as $column) {
            $parts[] = $column.' LIKE ?';
            $bindings[] = $like;
        }

        return [
            'sql' => '('.implode(' OR ', $parts).')',
            'bindings' => $bindings,
        ];
    }

    /**
     * sqlOr plus phone digit-variant OR matches.
     *
     * @param  list<string>  $columns
     * @param  list<string>  $phoneColumns
     * @return array{sql: string, bindings: list<string>}
     */
    public static function sqlFlexibleOr(array $columns, string $term, array $phoneColumns = []): array
    {
        $fragment = self::sqlOr($columns, $term);
        $digits = self::digits($term);
        if ($digits === null || $phoneColumns === []) {
            return $fragment;
        }

        $parts = [$fragment['sql']];
        $bindings = $fragment['bindings'];
        foreach (self::phoneDigitVariants($digits) as $variant) {
            $like = self::wildcard($variant);
            foreach ($phoneColumns as $column) {
                $parts[] = $column.' LIKE ?';
                $bindings[] = $like;
            }
        }

        return [
            'sql' => '('.implode(' OR ', $parts).')',
            'bindings' => $bindings,
        ];
    }

    /**
     * Personal + father + mother mobile columns.
     *
     * @return list<string>
     */
    public static function personPhoneColumns(string $ppn = 'ppn'): array
    {
        return [
            "{$ppn}.PersonPersonalMobileNumber",
            "{$ppn}.FatherMobileNumber",
            "{$ppn}.MotherMobileNumber",
        ];
    }

    /**
     * Common person list columns (directory / all-persons).
     *
     * @return list<string>
     */
    public static function personDirectoryColumns(string $pi = 'pi', string $ppn = 'ppn', string $q = 'q', string $sm = 'sm'): array
    {
        return array_merge(
            [
                "CAST({$pi}.PersonID AS CHAR)",
                "{$pi}.ShamandoraCode",
                "{$pi}.FirstName",
                "{$pi}.SecondName",
                "{$pi}.ThirdName",
                "{$pi}.FourthName",
                "CONCAT_WS(' ', {$pi}.FirstName, {$pi}.SecondName, {$pi}.ThirdName, {$pi}.FourthName)",
                "{$q}.QetaaName",
                "{$sm}.SanaMarhalaName",
            ],
            self::personPhoneColumns($ppn),
        );
    }

    /**
     * Allowed-people subquery columns used by special-case / blacklist search.
     *
     * @return list<string>
     */
    public static function allowedPeopleColumns(string $alias = 'allowed_people'): array
    {
        return [
            "{$alias}.PersonName",
            "CAST({$alias}.PersonID AS CHAR)",
            "{$alias}.PersonPersonalMobileNumber",
            "{$alias}.FatherMobileNumber",
            "{$alias}.MotherMobileNumber",
            "{$alias}.FirstName",
            "{$alias}.SecondName",
            "{$alias}.ThirdName",
            "{$alias}.FourthName",
            "{$alias}.ShamandoraCode",
            "{$alias}.RaqamQawmy",
        ];
    }

    /**
     * Identity columns for person typeaheads (name parts + code + id + national id + phones).
     *
     * @return array{columns: list<string>, raw: list<string>}
     */
    public static function personIdentityFields(string $pi = 'pi', ?string $ppn = null): array
    {
        $columns = [
            "{$pi}.FirstName",
            "{$pi}.SecondName",
            "{$pi}.ThirdName",
            "{$pi}.FourthName",
            "{$pi}.ShamandoraCode",
            "{$pi}.PersonID",
            "{$pi}.RaqamQawmy",
        ];
        if ($ppn !== null) {
            $columns = array_merge($columns, self::personPhoneColumns($ppn));
        }

        // CAST must be raw — Query Builder would quote it as a column name.
        $raw = [
            "CONCAT_WS(' ', {$pi}.FirstName, {$pi}.SecondName, {$pi}.ThirdName, {$pi}.FourthName)",
            "CAST({$pi}.PersonID AS CHAR)",
        ];

        return ['columns' => $columns, 'raw' => $raw];
    }

    /**
     * Guest / family member typeahead fields.
     *
     * @return array{columns: list<string>, raw: list<string>}
     */
    public static function namedPartyFields(string $alias, string $idColumn, string $mobileColumn = 'MobileNumber'): array
    {
        return [
            'columns' => [
                "{$alias}.{$idColumn}",
                "{$alias}.{$mobileColumn}",
                "{$alias}.RaqamQawmy",
            ],
            'raw' => [
                "CONCAT_WS(' ', {$alias}.FirstName, {$alias}.SecondName, {$alias}.ThirdName, {$alias}.FourthName)",
                "CAST({$alias}.{$idColumn} AS CHAR)",
            ],
        ];
    }
}
