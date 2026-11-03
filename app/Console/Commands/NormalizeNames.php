<?php

namespace App\Console\Commands;

use App\Domain\Enrolment\LiveFormFieldNormalizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NormalizeNames extends Command
{
    protected $signature = 'names:normalize {--dry-run : Print changes without writing}';
    protected $description = 'Normalize existing names in database';

    public function handle(LiveFormFieldNormalizer $normalizer): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->normalizeTable('PersonInformation', $normalizer, $dryRun);
        $this->normalizeTable('NewUsersInformation', $normalizer, $dryRun);

        $this->info($dryRun ? 'Dry run finished. No rows were updated.' : 'Name normalization finished.');

        return self::SUCCESS;
    }

    private function normalizeTable(string $table, LiveFormFieldNormalizer $normalizer, bool $dryRun): void
    {
        DB::table($table)
            ->orderBy('PersonID')
            ->chunk(500, function ($rows) use ($table, $normalizer, $dryRun) {
                foreach ($rows as $row) {
                    $updates = [];
                    $fields = [
                        'FirstName' => $normalizer->normalizeArabicName($row->FirstName ?? null),
                        'SecondName' => $normalizer->normalizeArabicName($row->SecondName ?? null),
                        'ThirdName' => $normalizer->normalizeArabicName($row->ThirdName ?? null),
                        'FourthName' => $normalizer->normalizeArabicName($row->FourthName ?? null),
                    ];

                    foreach ($fields as $column => $normalized) {
                        $current = $row->{$column} ?? null;
                        if ($current === $normalized) {
                            continue;
                        }

                        $updates[$column] = $normalized;
                        if ($dryRun) {
                            $this->line(sprintf(
                                '%s #%s %s: %s => %s',
                                $table,
                                $row->PersonID,
                                $column,
                                $current === null ? 'NULL' : $current,
                                $normalized === null ? 'NULL' : $normalized,
                            ));
                        }
                    }

                    if ($updates !== [] && ! $dryRun) {
                        DB::table($table)
                            ->where('PersonID', $row->PersonID)
                            ->update($updates);
                    }
                }
            });
    }
}
