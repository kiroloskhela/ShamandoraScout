<?php

namespace App\Console\Commands;

use App\Domain\Enrolment\PlaceholderMedicalAnswerCleaner;
use Illuminate\Console\Command;

class PurgePlaceholderMedicalAnswers extends Command
{
    protected $signature = 'medical:purge-placeholder-answers';

    protected $description = 'Delete placeholder medical/allergy answers (لا, no, none, …) from enrolment and person tables';

    public function handle(PlaceholderMedicalAnswerCleaner $cleaner): int
    {
        $counts = $cleaner->run();

        $this->info(sprintf(
            'enrolment_updated=%d allergies_deleted=%d allergies_updated=%d history_deleted=%d history_updated=%d',
            $counts['enrolment_updated'],
            $counts['allergies_deleted'],
            $counts['allergies_updated'],
            $counts['history_deleted'],
            $counts['history_updated']
        ));

        return self::SUCCESS;
    }
}
