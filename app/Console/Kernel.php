<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('sanctum:prune-expired --hours=24')
            ->daily()
            ->appendOutputTo(storage_path('logs/sanctum_prune.log'));

        $schedule->command('queue:report-failed --threshold=1')
            ->hourly()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/failed_jobs_report.log'));

        $schedule->command('whatsapp:wake')
            ->everyTwoMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/whatsapp_wake.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }


}