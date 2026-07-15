<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Thin HTTP client for the Baileys WhatsApp bridge (/send).
 */
class WhatsAppBridgeClient
{
    public function normalizeEgNumber(string $input): string
    {
        $digits = preg_replace('/\D+/', '', $input ?? '');

        if ($digits === '') {
            return '+2';
        }

        if (str_starts_with($digits, '20')) {
            return '+' . $digits;
        }

        return '+2' . $digits;
    }

    /**
     * @return array{ok: true, to: mixed, messageId: mixed}
     */
    public function sendText(string $fullNumber, string $message): array
    {
        $normalized = $this->normalizeEgNumber($fullNumber);
        $url = (string) config('services.whatsapp.bridge_url');
        $token = (string) config('services.whatsapp.bridge_token');

        if ($url === '' || $token === '') {
            throw new RuntimeException('WhatsApp bridge is not configured.');
        }

        $res = Http::timeout(20)
            ->withHeaders(['X-Bridge-Token' => $token])
            ->post($url, [
                'full_number' => $normalized,
                'message' => $message,
            ]);

        if ($res->successful() && ($res->json('ok') === true)) {
            Log::info('WhatsApp sent', [
                'to' => $res->json('to'),
                'messageId' => $res->json('messageId'),
            ]);

            return [
                'ok' => true,
                'to' => $res->json('to'),
                'messageId' => $res->json('messageId'),
            ];
        }

        Log::warning('WhatsApp bridge failed', [
            'status' => $res->status(),
            'body' => $res->body(),
        ]);

        throw new RuntimeException('WhatsApp bridge error: ' . $res->body());
    }
}
