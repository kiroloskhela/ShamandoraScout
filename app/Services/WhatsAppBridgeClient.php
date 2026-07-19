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
        $digits = preg_replace('/\D+/', '', $input ?? '') ?? '';

        if ($digits === '') {
            return '+2';
        }

        // Already has Egypt country code: 20XXXXXXXXXX
        if (str_starts_with($digits, '20') && strlen($digits) >= 12) {
            return '+' . $digits;
        }

        // Local with leading 0: 01XXXXXXXXX → +201XXXXXXXXX
        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            return '+2' . $digits;
        }

        // Local without leading 0: 1XXXXXXXXX (10 digits) → +201XXXXXXXXX
        // Example: 1000485402 → +201000485402
        if (strlen($digits) === 10 && str_starts_with($digits, '1')) {
            return '+20' . $digits;
        }

        // Legacy fallback (keeps older 01… / short forms working via +2 prefix)
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

    /**
     * @return array{ok: true, to: mixed, messageId: mixed}
     */
    public function sendImage(string $fullNumber, string $imageBase64, string $caption = '', string $mimeType = 'image/png'): array
    {
        $normalized = $this->normalizeEgNumber($fullNumber);
        $url = (string) config('services.whatsapp.bridge_url');
        $token = (string) config('services.whatsapp.bridge_token');

        if ($url === '' || $token === '') {
            throw new RuntimeException('WhatsApp bridge is not configured.');
        }

        $res = Http::timeout(45)
            ->withHeaders(['X-Bridge-Token' => $token])
            ->post($url, [
                'full_number' => $normalized,
                'image_base64' => $imageBase64,
                'caption' => $caption,
                'mime_type' => $mimeType,
            ]);

        if ($res->successful() && ($res->json('ok') === true)) {
            Log::info('WhatsApp image sent', [
                'to' => $res->json('to'),
                'messageId' => $res->json('messageId'),
            ]);

            return [
                'ok' => true,
                'to' => $res->json('to'),
                'messageId' => $res->json('messageId'),
            ];
        }

        Log::warning('WhatsApp bridge image failed', [
            'status' => $res->status(),
            'body' => $res->body(),
        ]);

        throw new RuntimeException('WhatsApp bridge error: ' . $res->body());
    }
}
