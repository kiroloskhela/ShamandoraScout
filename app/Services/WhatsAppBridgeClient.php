<?php

namespace App\Services;

use App\Support\AppSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

/**
 * HTTP client for the Baileys WhatsApp bridge (/send, /health, /reconnect).
 */
class WhatsAppBridgeClient
{
    public function isEnabled(): bool
    {
        return AppSettings::whatsappEnabled();
    }

    /**
     * Egyptian mobiles only. Accepts 01XXXXXXXXX, 1XXXXXXXXX, +20…, 0020…,
     * and a trunk zero written after the country code (+20 0…).
     */
    public function normalizeEgNumber(string $input): string
    {
        $digits = preg_replace('/\D+/', '', $input) ?? '';

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        // +20 0XXXXXXXXX arrives as 200 + 10 national digits.
        if (str_starts_with($digits, '200') && strlen($digits) === 13) {
            $digits = '20'.substr($digits, 3);
        }

        if (str_starts_with($digits, '20') && strlen($digits) === 12 && str_starts_with($digits, '201')) {
            return '+'.$digits;
        }

        if (str_starts_with($digits, '0') && strlen($digits) === 11 && str_starts_with($digits, '01')) {
            return '+2'.$digits;
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '1')) {
            return '+20'.$digits;
        }

        throw new InvalidArgumentException('Invalid Egyptian mobile number.');
    }

    /**
     * @return array{ok: true, skipped: true, to: null, messageId: null}
     */
    private function skippedSend(): array
    {
        Log::info('WhatsApp send skipped because the service is off');

        return [
            'ok' => true,
            'skipped' => true,
            'to' => null,
            'messageId' => null,
        ];
    }

    /**
     * @return array{ok: true, to: mixed, messageId: mixed}
     */
    public function sendText(string $fullNumber, string $message, ?string $idempotencyKey = null): array
    {
        if (! $this->isEnabled()) {
            return $this->skippedSend();
        }

        $normalized = $this->normalizeEgNumber($fullNumber);
        $url = (string) config('services.whatsapp.bridge_url');
        $token = (string) config('services.whatsapp.bridge_token');

        if ($url === '' || $token === '') {
            throw new RuntimeException('WhatsApp bridge is not configured.');
        }

        $headers = ['X-Bridge-Token' => $token];
        if ($idempotencyKey !== null && $idempotencyKey !== '') {
            $headers['X-Idempotency-Key'] = $idempotencyKey;
        }

        $res = Http::timeout(45)
            ->withHeaders($headers)
            ->post($url, [
                'full_number' => $normalized,
                'message' => $message,
                'idempotency_key' => $idempotencyKey,
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

        throw new RuntimeException('WhatsApp bridge error HTTP '.$res->status().': '.$res->body());
    }

    /**
     * @return array{ok: true, to: mixed, messageId: mixed}
     */
    public function sendImage(string $fullNumber, string $imageBase64, string $caption = '', string $mimeType = 'image/png', ?string $idempotencyKey = null): array
    {
        if (! $this->isEnabled()) {
            return $this->skippedSend();
        }

        $normalized = $this->normalizeEgNumber($fullNumber);
        $url = (string) config('services.whatsapp.bridge_url');
        $token = (string) config('services.whatsapp.bridge_token');

        if ($url === '' || $token === '') {
            throw new RuntimeException('WhatsApp bridge is not configured.');
        }

        $headers = ['X-Bridge-Token' => $token];
        if ($idempotencyKey !== null && $idempotencyKey !== '') {
            $headers['X-Idempotency-Key'] = $idempotencyKey;
        }

        $res = Http::timeout(45)
            ->withHeaders($headers)
            ->post($url, [
                'full_number' => $normalized,
                'image_base64' => $imageBase64,
                'caption' => $caption,
                'mime_type' => $mimeType,
                'idempotency_key' => $idempotencyKey,
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

        throw new RuntimeException('WhatsApp bridge error HTTP '.$res->status().': '.$res->body());
    }

    public function baseUrl(): string
    {
        $explicit = config('services.whatsapp.bridge_base_url');
        if (is_string($explicit) && $explicit !== '') {
            return rtrim($explicit, '/');
        }

        $sendUrl = (string) config('services.whatsapp.bridge_url', 'http://127.0.0.1:3010/send');
        $base = preg_replace('#/send/?$#', '', $sendUrl);

        return $base !== '' ? rtrim((string) $base, '/') : 'http://127.0.0.1:3010';
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchQr(): array
    {
        $base = $this->baseUrl();
        $token = (string) config('services.whatsapp.bridge_token');
        $request = Http::timeout(5);
        if ($token !== '') {
            $request = $request->withHeaders(['X-Bridge-Token' => $token]);
        }

        $res = $request->get($base.'/qr');
        if (! $res->successful()) {
            throw new RuntimeException('WhatsApp bridge QR HTTP '.$res->status());
        }

        return $res->json() ?? [];
    }

    public function assertLoopbackBaseUrl(): string
    {
        $base = $this->baseUrl();
        $host = strtolower((string) parse_url($base, PHP_URL_HOST));
        $allowed = ['127.0.0.1', 'localhost', '::1'];
        if (! in_array($host, $allowed, true)) {
            throw new RuntimeException('WhatsApp reconnect is only allowed to a local bridge.');
        }

        return $base;
    }

    /**
     * @return array<string, mixed>
     */
    public function reconnectSavedSession(): array
    {
        $base = $this->assertLoopbackBaseUrl();
        $token = (string) config('services.whatsapp.bridge_token');
        if ($token === '') {
            throw new RuntimeException('WhatsApp bridge is not configured.');
        }

        $res = Http::timeout(20)
            ->withHeaders(['X-Bridge-Token' => $token])
            ->post($base.'/reconnect');

        if ($res->successful() && $res->json('ok') === true) {
            return $res->json() ?? ['ok' => true];
        }

        Log::warning('WhatsApp bridge reconnect failed', [
            'status' => $res->status(),
            'body' => $res->body(),
        ]);

        throw new RuntimeException('WhatsApp bridge reconnect error: ' . $res->body());
    }
}
