<?php

namespace App\Support;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Server-side pagination for raw SQL SELECT lists.
 */
class SqlPaginator
{
    /**
     * @param  list<mixed>  $bindings
     * @param  list<mixed>|null  $countBindings  Defaults to $bindings when omitted.
     */
    public static function paginate(
        string $sql,
        array $bindings = [],
        int $perPage = 25,
        ?string $countSql = null,
        ?array $countBindings = null,
    ): LengthAwarePaginator {
        $perPage = max(1, $perPage);

        if ($countSql === null) {
            // Strip trailing ORDER BY for cheaper/safer counts; derived tables still need unique column names.
            $countInner = preg_replace('/\s+ORDER\s+BY\s+.+$/is', '', trim($sql)) ?? trim($sql);
            $countSql = 'SELECT COUNT(*) AS aggregate FROM ('.$countInner.') AS pagination_count_sub';
        }

        $total = (int) (DB::selectOne($countSql, $countBindings ?? $bindings)->aggregate ?? 0);

        $page = max(1, LengthAwarePaginator::resolveCurrentPage());
        $lastPage = max(1, (int) ceil($total / $perPage));
        if ($page > $lastPage) {
            $page = $lastPage;
        }

        $offset = ($page - 1) * $perPage;
        if (! is_int($offset) || $offset < 0) {
            return new LengthAwarePaginator(
                [],
                $total,
                $perPage,
                1,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'query' => request()->query(),
                ]
            );
        }

        $pageSql = $sql.' LIMIT ? OFFSET ?';
        $rows = DB::select($pageSql, array_merge($bindings, [$perPage, $offset]));

        return new LengthAwarePaginator(
            $rows,
            $total,
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]
        );
    }
}
