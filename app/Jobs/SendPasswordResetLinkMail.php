<?php

namespace App\Jobs;

use App\Services\BrevoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendPasswordResetLinkMail implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [10, 60, 300];

    public function __construct(
        public string $toEmail,
        public string $toName,
        public string $personId,
        public string $resetUrl,
        public string $logoUrl,
        public int $expireMinutes = 60,
    ) {
    }

    public function handle(BrevoService $brevo): void
    {
        $brevo->sendPasswordResetLinkBilingual(
            $this->toEmail,
            $this->toName,
            $this->personId,
            $this->resetUrl,
            $this->logoUrl,
            $this->expireMinutes
        );
    }

    public function failed(Throwable $e): void
    {
        Log::error('Password reset email failed permanently', [
            'person_id' => $this->personId,
            'error' => $e->getMessage(),
        ]);
    }
}
