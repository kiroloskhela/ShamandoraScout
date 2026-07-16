<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Sentry\SentrySdk;
use Sentry\Severity;
use Throwable;

class ReportFailedJobs extends Command
{
    protected $signature = 'queue:report-failed {--threshold=1 : Alert when failed_jobs count is at least this}';

    protected $description = 'Log (and optionally Sentry-report) when the failed_jobs table has rows';

    public function handle(): int
    {
        if (! Schema::hasTable('failed_jobs')) {
            $this->warn('failed_jobs table missing — skipping.');

            return self::SUCCESS;
        }

        $threshold = max(1, (int) $this->option('threshold'));
        $count = (int) DB::table('failed_jobs')->count();

        if ($count < $threshold) {
            $this->info("failed_jobs={$count} (below threshold {$threshold})");

            return self::SUCCESS;
        }

        $latest = DB::table('failed_jobs')
            ->orderByDesc('id')
            ->limit(5)
            ->get(['id', 'queue', 'failed_at']);

        $message = "Queue alert: {$count} failed job(s) (threshold {$threshold}).";

        Log::error($message, [
            'failed_jobs_count' => $count,
            'latest' => $latest->toArray(),
        ]);

        if (app()->bound('sentry') || class_exists(SentrySdk::class)) {
            try {
                if (function_exists('\\Sentry\\captureMessage')) {
                    \Sentry\captureMessage($message, Severity::error());
                }
            } catch (Throwable) {
                // Sentry optional
            }
        }

        $this->error($message);

        return self::FAILURE;
    }
}
