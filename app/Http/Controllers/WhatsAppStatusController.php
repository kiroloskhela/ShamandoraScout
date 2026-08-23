<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppBridgeClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsAppStatusController extends Controller
{
    public function index(WhatsAppBridgeClient $bridge)
    {
        $base = $bridge->baseUrl();
        $connected = false;
        $qr = null;
        $reachable = false;
        $error = null;
        $hasReusableSession = false;
        $pairingRequired = false;
        $reconnecting = false;

        try {
            $health = Http::timeout(5)->get(rtrim($base, '/') . '/health');
            if ($health->successful()) {
                $reachable = true;
                $connected = (bool) $health->json('connected');
                $hasReusableSession = (bool) $health->json('hasReusableSession');
                $pairingRequired = (bool) $health->json('pairingRequired');
                $reconnecting = (bool) $health->json('reconnecting');
            } else {
                $error = 'Bridge health returned HTTP ' . $health->status();
            }
        } catch (Throwable $e) {
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
            } catch (Throwable $e) {
                Log::warning('WhatsApp bridge QR failed', ['error' => $e->getMessage()]);
            }
        }

        return view('whatsapp.status', compact(
            'connected',
            'qr',
            'reachable',
            'error',
            'base',
            'hasReusableSession',
            'pairingRequired',
            'reconnecting',
        ));
    }

    public function reconnect(WhatsAppBridgeClient $bridge): RedirectResponse
    {
        try {
            $bridge->reconnectSavedSession();

            return redirect()
                ->route('whatsapp.status')
                ->with('success', __('Reconnecting the saved WhatsApp session. Refresh in a few seconds.'));
        } catch (Throwable $e) {
            Log::warning('WhatsApp reconnect request failed', ['error' => $e->getMessage()]);

            return redirect()
                ->route('whatsapp.status')
                ->with('error', __('Could not reconnect WhatsApp. Check that the bridge is running.'));
        }
    }
}
