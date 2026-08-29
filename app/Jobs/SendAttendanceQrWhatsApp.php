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
        public string $entityType,
        public int $entityId,
        public ?string $eventName = null,
    ) {}

    /**
     * Backward-compatible factory for person-only callers.
     */
    public static function forPerson(int $personId, ?string $eventName = null): self
    {
        return new self(AttendanceQrService::TYPE_PERSON, $personId, $eventName);
    }

    public function handle(AttendanceQrService $qr): void
    {
        if (! $qr->shouldSendViaWhatsApp()) {
            return;
        }

        $qr->sendEntityQrViaWhatsApp($this->entityType, $this->entityId, $this->eventName);
    }

    public function failed(?Throwable $e): void
    {
        Log::warning('Attendance QR WhatsApp job failed', [
            'entityType' => $this->entityType,
            'entityId' => $this->entityId,
            'error' => $e?->getMessage(),
        ]);
    }
}
