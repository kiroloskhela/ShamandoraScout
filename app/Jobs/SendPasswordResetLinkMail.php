<?php

namespace App\Jobs;

use App\Services\BrevoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPasswordResetLinkMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
}
