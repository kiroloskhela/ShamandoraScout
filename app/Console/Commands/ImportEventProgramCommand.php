<?php

namespace App\Console\Commands;

use App\Domain\EventProgram\EventProgramImporter;
use App\Domain\EventProgram\GoogleSheetFetcher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class ImportEventProgramCommand extends Command
{
    protected $signature = 'event-program:import
        {seasonEventId : SeasonEventID}
        {--file= : Path to guide xlsx}
        {--url= : Google Sheets share URL}
        {--answers= : JSON object of question_id => answer}
        {--commit : Commit after import (auto-answer empty if ready)}
        {--created-by= : PersonID acting as importer}';

    protected $description = 'Import a camp leader program from xlsx or Google Sheets URL';

    public function handle(EventProgramImporter $importer, GoogleSheetFetcher $sheets): int
    {
        $seasonEventId = (int) $this->argument('seasonEventId');
        $createdBy = (int) ($this->option('created-by') ?: (Auth::id() ?: 1));
        $file = $this->option('file');
        $url = $this->option('url');

        if (! $file && ! $url) {
            $this->error('Provide --file= or --url=');

            return self::FAILURE;
        }

        try {
            if ($url) {
                $tmp = storage_path('app/tmp/event_program_'.uniqid('', true).'.xlsx');
                $sheets->downloadXlsx((string) $url, $tmp);
                $session = $importer->startFromXlsx($seasonEventId, $tmp, $createdBy, 'cli');
                @unlink($tmp);
            } else {
                $session = $importer->startFromXlsx($seasonEventId, (string) $file, $createdBy, 'cli');
            }
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Import session #'.$session->id.' status='.$session->status);
        $questions = $session->questions_json ?? [];
        if ($questions !== []) {
            $this->warn(count($questions).' clarifying question(s):');
            foreach ($questions as $q) {
                $this->line('- ['.$q['id'].'] '.$q['prompt']);
            }
        }

        $answersJson = $this->option('answers');
        $answers = [];
        if (is_string($answersJson) && $answersJson !== '') {
            $answers = json_decode($answersJson, true) ?: [];
            $importer->answer($session, $answers);
            $this->info('Answers applied.');
        }

        if ($this->option('commit')) {
            if ($session->status === 'pending_review' && $answers === []) {
                $this->error('Cannot commit while questions are unanswered. Pass --answers=JSON');

                return self::FAILURE;
            }
            $program = $importer->commit($session->fresh());
            $this->info('Committed program #'.$program->id.' ('.$program->title.')');
        }

        return self::SUCCESS;
    }
}
