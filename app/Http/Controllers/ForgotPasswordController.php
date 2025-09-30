<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request as HttpRequest; // for your WhatsApp bridge

class ForgotPasswordController extends Controller
{
    /**
     * Show the "forgot password" form (optional if you already have a blade).
     */
    public function showForm()
    {
        return view('forgot-password.form'); // resources/views/forgot-password.blade.php
    }

    /**
     * Handle reset request: verify phone + DOB, send WhatsApp, then hash+save.
     */


public function handle(Request $request)
{
    // 1) Strict validation (phone = local 11 digits; DOB = YYYY-MM-DD)
    $request->validate([
        'phone' => ['required','regex:/^0\d{10}$/'],
        'dob'   => ['required','date_format:Y-m-d'],
    ], [
        'phone.regex'     => 'Phone must be 11 digits and start with 0 (e.g. 01000485402).',
        'dob.date_format' => 'DOB must be YYYY-MM-DD (e.g. 2001-03-29).',
    ]);

    $normalizedPhone = preg_replace('/\D+/', '', (string) $request->input('phone')); // 010XXXXXXXXX
    $dobInput        = (string) $request->input('dob'); // 'YYYY-MM-DD'

    // 2) Match ON phone + DOB (the unique combo)
    // If DateOfBirth is DATETIME, change the where() line to whereDate('pi.DateOfBirth', $dobInput)
    $matches = DB::table('PersonInformation as pi')
        ->join('PersonPhoneNumbers as ppn', 'ppn.PersonID', '=', 'pi.PersonID')
        ->whereRaw('TRIM(ppn.PersonPersonalMobileNumber) = ?', [$normalizedPhone])
        ->where('pi.DateOfBirth', '=', $dobInput)   // or ->whereDate('pi.DateOfBirth', $dobInput)
        ->select('pi.PersonID','pi.FirstName','pi.SecondName','pi.ThirdName','pi.FourthName')
        ->limit(2) // detect accidental duplicates defensively
        ->get();

    if ($matches->isEmpty()) {
        return back()->with('error', 'لم يتم العثور على مستخدم يطابق رقم الهاتف وتاريخ الميلاد.')->withInput();
    }
    if ($matches->count() > 1) {
        // Shouldn't happen if (phone + DOB) is unique; handle safely
        return back()->with('error', 'يوجد أكثر من مستخدم بنفس رقم الهاتف وتاريخ الميلاد. يرجى التواصل مع الدعم.');
    }

    $person   = $matches->first();
    $personId = $person->PersonID;

    // 3) Generate 8-char password (your exact method)
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = [];
    $alphaLength = strlen($alphabet) - 1;
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    $plainPassword = implode($pass);

    // 4) WhatsApp message (send first; abort on failure)
    $fullName = trim(implode(' ', array_filter([
        $person->FirstName  ?? '',
        $person->SecondName ?? '',
        $person->ThirdName  ?? '',
        $person->FourthName ?? '',
    ])));

    try {
        $payload = [
            'full_number' => $normalizedPhone,
            'message'     => "اهلا بك يا {$fullName}\n"
                           . "الرقم الخاص بك: {$personId}\n"
                           . "الرقم: {$plainPassword}\n"
                           . "يرجى تغيير الرقم عند أول تسجيل دخول.\n"
                           . "مرحبا بك في الكشافة.",
        ];
        $fake = \Illuminate\Http\Request::create('/whatsapp/send', 'POST', $payload);
        app(\App\Http\Controllers\WhatsAppBridgeController::class)->send($fake);
    } catch (\Throwable $e) {
        Log::error('WhatsApp send failed; aborting update', ['person_id' => $personId, 'error' => $e->getMessage()]);
        return back()->with('error', 'Failed to send WhatsApp. Password was NOT updated.');
    }

    // 5) Hash + upsert in PersonSystemPassword
    DB::table('PersonSystemPassword')->updateOrInsert(
        ['PersonID' => $personId],
        ['Password' => Hash::make($plainPassword), 'PasswordCreationTimestamp' => now()]
    );

    return back()->with('success', 'تم إرسال كلمة مرور مؤقتة على واتساب وتحديثها في النظام.');
}


    /**
     * Normalize phone number to a consistent format.
     * Example: strip non-digits; if Egyptian local starting with 0, convert to +20.
     * Tune for your own storage rules.
     */
    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone ?? '');

        // If already starts with country code like 20..., keep it; else if local 0XXXXXXXXX -> +20XXXXXXXXX
        if (Str::startsWith($digits, '20')) {
            return '+'.$digits;
        }
        if (Str::startsWith($digits, '0') && strlen($digits) >= 10) {
            return '+20'.ltrim($digits, '0');
        }
        // Fallback: just prefix + if missing
        return Str::startsWith($digits, '+') ? $digits : '+'.$digits;
    }
}