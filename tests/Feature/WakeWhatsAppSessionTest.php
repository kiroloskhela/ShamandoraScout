<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WakeWhatsAppSessionTest extends TestCase
{
    public function test_wake_reconnects_when_session_exists_and_disconnected(): void
    {
        config([
            'services.whatsapp.bridge_base_url' => 'http://127.0.0.1:3010',
            'services.whatsapp.bridge_token' => 'test-token',
        ]);

        Http::fake([
            'http://127.0.0.1:3010/health' => Http::response([
                'ok' => true,
                'connected' => false,
                'hasReusableSession' => true,
                'pairingRequired' => false,
                'reconnecting' => false,
            ], 200),
            'http://127.0.0.1:3010/reconnect' => Http::response(['ok' => true], 200),
        ]);

        $this->artisan('whatsapp:wake')->assertSuccessful();

        Http::assertSent(function ($request) {
            return $request->url() === 'http://127.0.0.1:3010/reconnect'
                && $request->method() === 'POST';
        });
    }

    public function test_wake_skips_when_pairing_required(): void
    {
        config([
            'services.whatsapp.bridge_base_url' => 'http://127.0.0.1:3010',
            'services.whatsapp.bridge_token' => 'test-token',
        ]);

        Http::fake([
            'http://127.0.0.1:3010/health' => Http::response([
                'ok' => true,
                'connected' => false,
                'hasReusableSession' => true,
                'pairingRequired' => true,
                'reconnecting' => false,
            ], 200),
        ]);

        $this->artisan('whatsapp:wake')->assertSuccessful();
        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), '/reconnect');
        });
    }

    public function test_wake_skips_when_already_reconnecting(): void
    {
        config([
            'services.whatsapp.bridge_base_url' => 'http://127.0.0.1:3010',
            'services.whatsapp.bridge_token' => 'test-token',
        ]);

        Http::fake([
            'http://127.0.0.1:3010/health' => Http::response([
                'ok' => true,
                'connected' => false,
                'hasReusableSession' => true,
                'pairingRequired' => false,
                'reconnecting' => true,
            ], 200),
        ]);

        $this->artisan('whatsapp:wake')->assertSuccessful();
        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), '/reconnect');
        });
    }

    public function test_wake_refuses_non_local_bridge(): void
    {
        config([
            'services.whatsapp.bridge_base_url' => 'https://evil.example/wa',
            'services.whatsapp.bridge_token' => 'test-token',
        ]);

        Http::fake();

        $this->artisan('whatsapp:wake')->assertSuccessful();
        Http::assertNothingSent();
    }
}
