<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class LookupCache
{
    public const TTL_SECONDS = 3600;

    /**
     * Catalog tables with no LookupTableController CRUD (liveform / person forms).
     *
     * @var list<string>
     */
    private const EXTRA_TABLES = ['QuestionsTypes'];

    public static function all(string $table): Collection
    {
        self::assertAllowed($table);

        try {
            $rows = Cache::remember(self::key($table), self::TTL_SECONDS, function () use ($table) {
                return DB::table($table)->get()->all();
            });

            return collect($rows)->map(fn ($row) => is_object($row) ? clone $row : $row);
        } catch (Throwable) {
            return DB::table($table)->get();
        }
    }

    public static function ordered(string $table, string $column, string $direction = 'asc'): Collection
    {
        $descending = strtolower($direction) === 'desc';

        return self::all($table)
            ->sortBy($column, SORT_REGULAR, $descending)
            ->values();
    }

    public static function forget(string $table): void
    {
        self::assertAllowed($table);

        try {
            Cache::forget(self::key($table));
        } catch (Throwable) {
            // Next read will miss or fall back to MySQL.
        }
    }

    /**
     * @return list<string>
     */
    public static function tables(): array
    {
        return array_values(array_unique([
            ...array_filter(array_column(config('lookups', []), 'table')),
            ...self::EXTRA_TABLES,
        ]));
    }

    private static function key(string $table): string
    {
        return 'lookup.'.$table;
    }

    private static function assertAllowed(string $table): void
    {
        if (! in_array($table, self::tables(), true)) {
            throw new InvalidArgumentException("Lookup cache does not allow table [{$table}].");
        }
    }
}
