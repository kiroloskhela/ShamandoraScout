<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppStatusController extends Controller
{
    public function index()
    {
        $base = $this->bridgeBaseUrl();
        $connected = false;
        $qr = null;
        $reachable = false;
        $error = null;

        try {
            $health = Http::timeout(5)->get(rtrim($base, '/') . '/health');
            if ($health->successful()) {
                $reachable = true;
                $connected = (bool) $health->json('connected');
            } else {
                $error = 'Bridge health returned HTTP ' . $health->status();
            }
        } catch (\Throwable $e) {
            Log::warning('WhatsApp bridge health failed', ['error' => $e->getMessage()]);
            $error = 'تعذر الاتصال بجسر الواتساب. تأكد أن الخدمة تعمل على الخادم.';
        }

        if ($reachable && !$connected) {
            try {
                $qrRes = Http::timeout(5)->get(rtrim($base, '/') . '/qr');
                if ($qrRes->successful()) {
                    $qr = $qrRes->json('qr');
                    $connected = (bool) ($qrRes->json('connected') ?? $connected);
                }
            } catch (\Throwable $e) {
                Log::warning('WhatsApp bridge QR failed', ['error' => $e->getMessage()]);
            }
        }

        return view('whatsapp.status', compact('connected', 'qr', 'reachable', 'error', 'base'));
    }

    private function bridgeBaseUrl(): string
    {
        $explicit = env('WHATSAPP_BRIDGE_BASE_URL');
        if (is_string($explicit) && $explicit !== '') {
            return rtrim($explicit, '/');
        }

        $sendUrl = (string) env('WHATSAPP_BRIDGE_URL', 'http://127.0.0.1:3000/send');
        $base = preg_replace('#/send/?$#', '', $sendUrl);

        return $base !== '' ? rtrim($base, '/') : 'http://127.0.0.1:3000';
    }
}
