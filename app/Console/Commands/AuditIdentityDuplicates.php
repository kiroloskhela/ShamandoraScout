<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Read-only audit of duplicate identity values (RaqamQawmy / ShamandoraCode)
 * across PersonInformation and the enrolment staging tables.
 *
 * Run this BEFORE applying any migration that adds a UNIQUE index on these
 * columns. It never deletes or modifies data.
 */
class AuditIdentityDuplicates extends Command
{
    protected $signature = 'scout:audit-identity-duplicates {--limit=20 : Max example groups to print per check}';

    protected $description = 'Report duplicate RaqamQawmy / ShamandoraCode values on PersonInformation and the NewUsers enrolment/waitinglist staging tables. Read-only, deletes nothing.';

    /**
     * @var array<int, array{table:string, column:string}>
     */
    private array $checks = [
        ['table' => 'PersonInformation', 'column' => 'RaqamQawmy'],
        ['table' => 'PersonInformation', 'column' => 'ShamandoraCode'],
        ['table' => 'NewUsersInformation', 'column' => 'RaqamQawmy'],
        ['table' => 'NewUsersInformationWaitinglist', 'column' => 'RaqamQawmy'],
    ];

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $totalDuplicateGroups = 0;
        $rows = [];

        foreach ($this->checks as $check) {
            $table = $check['table'];
            $column = $check['column'];

            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                $rows[] = [$table, $column, 'SKIPPED (table/column not found)', '-'];
                continue;
            }

            $duplicateGroups = DB::table($table)
                ->select($column, DB::raw('COUNT(*) as duplicate_count'))
                ->whereNotNull($column)
                ->where($column, '!=', '')
                ->groupBy($column)
                ->having('duplicate_count', '>', 1)
                ->orderByDesc('duplicate_count')
                ->get();

            $groupCount = $duplicateGroups->count();
            $affectedRowCount = (int) $duplicateGroups->sum('duplicate_count');

            $totalDuplicateGroups += $groupCount;

            $rows[] = [
                $table,
                $column,
                $groupCount === 0 ? 'OK' : "{$groupCount} duplicate value(s)",
                $affectedRowCount,
            ];

            if ($groupCount > 0) {
                $this->newLine();
                $this->warn("Duplicate {$column} values in {$table} (showing up to {$limit}):");
                $this->table(
                    [$column, 'count'],
                    $duplicateGroups->take($limit)->map(fn ($row) => [$row->$column, $row->duplicate_count])->toArray()
                );
            }
        }

        $this->newLine();
        $this->info('Summary:');
        $this->table(['Table', 'Column', 'Status', 'Rows involved'], $rows);

        if ($totalDuplicateGroups > 0) {
            $this->newLine();
            $this->error(
                "Found {$totalDuplicateGroups} duplicate value group(s) above. ".
                'Resolve these BEFORE running migrations that add UNIQUE indexes on these columns, '.
                'otherwise the migration will fail (by design).'
            );

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('No duplicates found. Safe to run the identity-hardening migrations.');

        return self::SUCCESS;
    }
}
