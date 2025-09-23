<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppBridgeController extends Controller
{

    
    public function send(Request $request)
{
    $data = $request->validate([
        'full_number' => 'required|string',
        'message'     => 'required|string|max:4096',
    ]);

    // --- Normalize number and add +2 if missing ---
    $number = preg_replace('/\D+/', '', $data['full_number']); // keep digits only

    // If already starts with "20..." keep it, else add "20"
    if (str_starts_with($number, '20')) {
        $normalized = '+'.$number;
    } else {
        $normalized = '+2'.$number;
    }

    $payload = [
        'full_number' => $normalized,
        'message'     => $data['message'],
    ];

    try {
        $res = Http::timeout(20)
            ->withHeaders(['X-Bridge-Token' => env('WHATSAPP_BRIDGE_TOKEN')])
            ->post(env('WHATSAPP_BRIDGE_URL'), $payload);

        if ($res->successful() && $res->json('ok') === true) {
            Log::info('WhatsApp sent', $res->json());
            return back()->with('success', 'Message sent via WhatsApp.');
        }

        Log::warning('Bridge failed', ['status' => $res->status(), 'body' => $res->body()]);
        return back()->with('error', 'Bridge error: '.$res->body());
    } catch (\Throwable $e) {
        Log::error('Bridge exception', ['error' => $e->getMessage()]);
        return back()->with('error', 'Bridge exception: '.$e->getMessage());
    }
}

}