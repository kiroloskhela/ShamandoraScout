<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Allocate the next integer PK for legacy tables that are not AUTO_INCREMENT.
 * Prefer real AUTO_INCREMENT when available; this exists for production-safe inserts.
 */
class ManualPrimaryKey
{
    public static function next(string $table, string $column): int
    {
        $last = DB::table($table)->orderByDesc($column)->value($column);

        return $last ? ((int) $last + 1) : 1;
    }
}
