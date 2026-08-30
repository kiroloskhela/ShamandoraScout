<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Server-side column filters for paginated data-tables (query key: f[ColumnKey]=value).
 */
class TableColumnFilters
{
    /**
     * @param  list<string>  $allowedKeys
     * @return array<string, string>
     */
    public static function fromRequest(Request $request, array $allowedKeys): array
    {
        $raw = $request->input('f', []);
        if (! is_array($raw)) {
            return [];
        }

        $allowed = array_fill_keys($allowedKeys, true);
        $out = [];

        foreach ($raw as $key => $value) {
            if (! is_string($key) || ! isset($allowed[$key])) {
                continue;
            }
            if (! is_scalar($value)) {
                continue;
            }
            $value = trim((string) $value);
            if ($value === '' || $value === '__all__') {
                continue;
            }
            if (mb_strlen($value) > 100) {
                $value = mb_substr($value, 0, 100);
            }
            $out[$key] = $value;
        }

        return $out;
    }

    /**
     * Append equality predicates for raw SQL lists.
     *
     * @param  array<string, string>  $filters
     * @param  array<string, string>  $columnMap  filter key => SQL expression
     * @return array{sql: string, bindings: list<string>}
     */
    public static function sqlEquals(array $filters, array $columnMap): array
    {
        $parts = [];
        $bindings = [];

        foreach ($filters as $key => $value) {
            if (! isset($columnMap[$key])) {
                continue;
            }
            $parts[] = $columnMap[$key].' = ?';
            $bindings[] = $value;
        }

        return [
            'sql' => $parts === [] ? '' : implode(' AND ', $parts),
            'bindings' => $bindings,
        ];
    }
}
