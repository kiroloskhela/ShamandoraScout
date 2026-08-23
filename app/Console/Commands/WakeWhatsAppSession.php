<?php

namespace App\Console\Commands;

use App\Services\WhatsAppBridgeClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class WakeWhatsAppSession extends Command
{
    protected $signature = 'whatsapp:wake';

    protected $description = 'Reconnect the WhatsApp bridge from the saved session if it dropped';

    public function handle(WhatsAppBridgeClient $bridge): int
    {
        try {
            $base = $bridge->assertLoopbackBaseUrl();
        } catch (RuntimeException $e) {
            $this->warn($e->getMessage());
            Log::warning('WhatsApp wake skipped', ['error' => $e->getMessage()]);

            return self::SUCCESS;
        }

        try {
            $health = Http::timeout(5)->get($base.'/health');
        } catch (Throwable $e) {
            $this->error('Bridge unreachable: '.$e->getMessage());
            Log::warning('WhatsApp wake: bridge unreachable', ['error' => $e->getMessage()]);

            return self::FAILURE;
        }

        if (! $health->successful()) {
            $this->error('Bridge health HTTP '.$health->status());

            return self::FAILURE;
        }

        $connected = (bool) $health->json('connected');
        $hasSession = (bool) $health->json('hasReusableSession');
        $pairingRequired = (bool) $health->json('pairingRequired');
        $reconnecting = (bool) $health->json('reconnecting');

        if ($connected) {
            $this->info('WhatsApp already connected.');

            return self::SUCCESS;
        }

        if ($pairingRequired) {
            $this->warn('Pairing required — not auto-reconnecting.');
            Log::warning('WhatsApp wake skipped: pairing required');

            return self::SUCCESS;
        }

        if ($reconnecting) {
            $this->info('Reconnect already in progress.');

            return self::SUCCESS;
        }

        if (! $hasSession) {
            $this->warn('No reusable session on disk.');

            return self::SUCCESS;
        }

        try {
            $bridge->reconnectSavedSession();
            $this->info('Asked the bridge to reconnect from the saved session.');
            Log::info('WhatsApp wake requested reconnect');

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            Log::warning('WhatsApp wake reconnect failed', ['error' => $e->getMessage()]);

            return self::FAILURE;
        }
    }
}
