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

        return '%' . $escaped . '%';
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
                    $sub->whereRaw($expression . ' LIKE ?', [$like]);
                    $first = false;
                } else {
                    $sub->orWhereRaw($expression . ' LIKE ?', [$like]);
                }
            }
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
            $parts[] = $column . ' LIKE ?';
            $bindings[] = $like;
        }

        return [
            'sql' => '(' . implode(' OR ', $parts) . ')',
            'bindings' => $bindings,
        ];
    }

    /**
     * Common person list columns (directory / all-persons).
     *
     * @return list<string>
     */
    public static function personDirectoryColumns(string $pi = 'pi', string $ppn = 'ppn', string $q = 'q', string $sm = 'sm'): array
    {
        return [
            "{$pi}.ShamandoraCode",
            "{$pi}.FirstName",
            "{$pi}.SecondName",
            "{$pi}.ThirdName",
            "{$pi}.FourthName",
            "{$ppn}.PersonPersonalMobileNumber",
            "{$q}.QetaaName",
            "{$sm}.SanaMarhalaName",
        ];
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
            "{$alias}.FirstName",
            "{$alias}.SecondName",
            "{$alias}.ThirdName",
            "{$alias}.FourthName",
            "{$alias}.ShamandoraCode",
            "{$alias}.RaqamQawmy",
        ];
    }

    /**
     * Identity columns for person typeaheads (name parts + code + id + national id + optional phone).
     *
     * @return array{columns: list<string>, raw: list<string>}
     */
    public static function personIdentityFields(string $pi = 'pi', ?string $phoneColumn = null): array
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
        if ($phoneColumn !== null) {
            $columns[] = $phoneColumn;
        }

        $raw = [
            "CONCAT_WS(' ', {$pi}.FirstName, {$pi}.SecondName, {$pi}.ThirdName, {$pi}.FourthName)",
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
            ],
        ];
    }
}
