<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppBridgeController extends Controller
{
    // === Public: original send (kept) ===
    public function send(Request $request)
    {
        $data = $request->validate([
            'full_number' => 'required|string',
            'message'     => 'required|string|max:4096',
        ]);

        $normalized = $this->normalizeEgNumber($data['full_number']); // +20...
        return $this->sendToBridge($normalized, $data['message']);
    }

    // === NEW: send with DB-based header ===
    public function sendWithHeader(Request $request)
    {
        $data = $request->validate([
            'full_number' => 'required|string',
            'message'     => 'required|string|max:4096',
        ]);

        $normalized = $this->normalizeEgNumber($data['full_number']); // +20...

        // Find person by phone (robust: tries multiple variants)
        $person = $this->findPersonByPhone($normalized);

        if (!$person) {
            return back()->with('error', 'لم يتم العثور على رقم الهاتف في قاعدة البيانات.');
        }

        // Build full display name
        $fullName = trim(
            implode(' ', array_filter([
                $person->FirstName ?? null,
                $person->SecondName ?? null,
                $person->ThirdName ?? null,
                $person->FourthName ?? null,
            ]))
        );

        // Detect gender (handles: 'female'/'male', 'F'/'M', 'انثى'/'ذكر')
        $g = mb_strtolower(trim((string)($person->Gender ?? '')));
        $isFemale = in_array($g, ['f','female','انثى','أنثى','اثنى','fem','بنات','فتاة'], true);
        $prefix = $isFemale ? 'شفتان ' : 'كابتن ';

        // Header: {prefix}{Name} + " اهلا بك يا ♡"
      $rtlMarker = "\u{202B}"; // Right-to-Left Embedding
        $header = $rtlMarker . "اهلا بك يا {$prefix}{$fullName} ♡ \n";

        $finalMessage = $header . $data['message'];

        return $this->sendToBridge($normalized, $finalMessage);
    }

    // --- Shared bridge caller ---
    private function sendToBridge(string $normalizedE164, string $message)
    {
        $payload = [
            'full_number' => $normalizedE164,  // Node expects +XXXXXXXX
            'message'     => $message,
        ];

        try {
            $res = Http::timeout(20)
                ->withHeaders(['X-Bridge-Token' => env('WHATSAPP_BRIDGE_TOKEN')])
                ->post(env('WHATSAPP_BRIDGE_URL'), $payload);

            if ($res->successful() && ($res->json('ok') === true)) {
                Log::info('WhatsApp sent', [
                    'to'        => $res->json('to'),
                    'messageId' => $res->json('messageId'),
                ]);
                return back()->with('success', 'تم إرسال رسالة الواتساب بنجاح.');
            }

            Log::warning('Bridge failed', ['status' => $res->status(), 'body' => $res->body()]);
            return back()->with('error', 'Bridge error: '.$res->body());

        } catch (\Throwable $e) {
            Log::error('Bridge exception', ['error' => $e->getMessage()]);
            return back()->with('error', 'Bridge exception: '.$e->getMessage());
        }
    }

    // --- Normalize Egyptian numbers to +20XXXXXXXXXX ---
    private function normalizeEgNumber(string $input): string
    {
        $digits = preg_replace('/\D+/', '', $input ?? '');

        if ($digits === '') {
            return '+2'; // degenerate but prevents nulls
        }

        // If already starts with 20... keep, else add 20
        if (str_starts_with($digits, '20')) {
            return '+'.$digits;
        }
        return '+2'.$digits;
    }


    private function findPersonByPhone(string $normalizedE164)
    {
        // Build variants
        $justDigits = preg_replace('/\D+/', '', $normalizedE164); // 20XXXXXXXXXX
        $local11    = '0'.substr($justDigits, -10);               // 01XXXXXXXXX (Egypt)
        $plusForm   = '+'.$justDigits;                            // +20XXXXXXXXXX

        // 1) find phone row
        $phoneRow = DB::table('PersonPhoneNumbers')
            ->where('PersonPersonalMobileNumber', $plusForm)
            ->orWhere('PersonPersonalMobileNumber', $local11)
            ->orWhere('PersonPersonalMobileNumber', $justDigits)
            // as a last resort, match by ending digits (e.g. last 10)
            ->orWhereRaw("RIGHT(REGEXP_REPLACE(PersonPersonalMobileNumber, '[^0-9]', ''), 10) = ?", [substr($justDigits, -10)])
            ->first();

        if (!$phoneRow) return null;

        // 2) fetch person info
        $pi = DB::table('PersonInformation')
            ->select('PersonID','FirstName','SecondName','ThirdName','FourthName','Gender')
            ->where('PersonID', $phoneRow->PersonID)
            ->first();

        return $pi ?: null;
    }
}