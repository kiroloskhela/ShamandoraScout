<?php

namespace App\Jobs;

use App\Services\FcmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendFcmNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  list<string>  $tokens
     */
    public function __construct(
        public array $tokens,
        public string $title,
        public string $body,
    ) {
    }

    public function handle(FcmService $fcm): void
    {
        if ($this->tokens === []) {
            return;
        }

        $fcm->sendToMultiple($this->tokens, $this->title, $this->body);
    }
}
