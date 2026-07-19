<?php

namespace App\Jobs;

use App\Services\AttendanceQrService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendAttendanceQrWhatsApp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $personId,
        public ?string $eventName = null,
    ) {}

    public function handle(AttendanceQrService $qr): void
    {
        $qr->sendQrViaWhatsApp($this->personId, $this->eventName);
    }

    public function failed(?Throwable $e): void
    {
        Log::warning('Attendance QR WhatsApp job failed', [
            'personId' => $this->personId,
            'error' => $e?->getMessage(),
        ]);
    }
}
