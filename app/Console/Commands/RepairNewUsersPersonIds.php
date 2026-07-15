<?php

namespace App\Console\Commands;

use App\Domain\Enrolment\WaitingListService;
use Illuminate\Console\Command;

class RepairNewUsersPersonIds extends Command
{
    protected $signature = 'enrolment:repair-person-ids
                            {--waiting-list : Repair NewUsersInformationWaitinglist instead of NewUsersInformation}
                            {--dry-run : Only report mismatches}';

    protected $description = 'Remint NewUsers PersonID/ShamandoraCode to match Package A surrogate id when safe';

    public function handle(WaitingListService $waitingList): int
    {
        $table = $this->option('waiting-list')
            ? 'NewUsersInformationWaitinglist'
            : 'NewUsersInformation';

        if ($this->option('dry-run')) {
            $count = \Illuminate\Support\Facades\DB::table($table)
                ->whereColumn('id', '!=', 'PersonID')
                ->count();
            $this->info("{$table}: {$count} mismatched row(s).");

            return self::SUCCESS;
        }

        $result = $waitingList->repairMismatchedPersonIds($table);
        $this->info("{$table}: fixed={$result['fixed']} skipped={$result['skipped']}");

        return self::SUCCESS;
    }
}
