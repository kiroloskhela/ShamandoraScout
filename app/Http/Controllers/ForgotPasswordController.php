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
    // Validate base inputs
    $baseRules = [
        'phone' => ['required','regex:/^0\d{10}$/'],   // 11 digits, starts with 0
        'dob'   => ['required','date_format:Y-m-d'],   // e.g., 2001-03-29
    ];

    // If modal posted RaqamQawmy, validate it too
    if ($request->filled('raqam_qawmy')) {
        $baseRules['raqam_qawmy'] = ['required','regex:/^\d{14}$/'];
    }

    $request->validate($baseRules, [
        'phone.regex'       => 'Phone must be 11 digits and start with 0 (e.g. 01000485402).',
        'dob.date_format'   => 'DOB must be YYYY-MM-DD (e.g., 2001-03-29).',
        'raqam_qawmy.regex' => 'RaqamQawmy must be 14 digits.',
    ]);

    $phone = preg_replace('/\D+/', '', (string) $request->input('phone'));
    $dob   = (string) $request->input('dob');           // 'YYYY-MM-DD'
    $nid   = $request->filled('raqam_qawmy') ? preg_replace('/\D+/', '', $request->input('raqam_qawmy')) : null;

    // Base query: phone + dob (your storage is exact 11-digit local)
    $q = DB::table('PersonInformation as pi')
        ->join('PersonPhoneNumbers as ppn', 'ppn.PersonID', '=', 'pi.PersonID')
        ->whereRaw('TRIM(ppn.PersonPersonalMobileNumber) = ?', [$phone])
        // if DateOfBirth is DATETIME use whereDate instead:
        ->where('pi.DateOfBirth', '=', $dob)
        ->select('pi.PersonID','pi.FirstName','pi.SecondName','pi.ThirdName','pi.FourthName');

    // If user provided RaqamQawmy (from modal), refine
    if ($nid) {
        $q->where('pi.RaqamQawmy', '=', $nid);
    }

    $matches = $q->limit(2)->get(); // limit(2) to detect duplicates safely

    // No match at all
    if ($matches->isEmpty()) {
        // If we already asked for RaqamQawmy, then this is an actual mismatch
        if ($nid) {
            return back()->with('error', 'البيانات لا تطابق أي مستخدم. يرجى التحقق من الرقم القومي.')
                         ->withInput();
        }
        return back()->with('error', 'لم يتم العثور على مستخدم يطابق رقم الهاتف وتاريخ الميلاد.')
                     ->withInput();
    }

    // More than one match and we don't yet have RaqamQawmy -> trigger modal
    if ($matches->count() > 1 && !$nid) {
        return back()
            ->with('need_raqam_qawmy', true) // <-- flag to open the modal
            ->with('info', 'يرجى إدخال الرقم القومي لتأكيد الهوية.')
            ->withInput(); // keep phone & dob for the modal
    }

    // Unique person (either from first pass, or after NID refining)
    $person   = $matches->first();
    $personId = $person->PersonID;

    // Generate 8-char password (your method)
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = [];
    $alphaLength = strlen($alphabet) - 1;
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    $plainPassword = implode($pass);

    // Send WhatsApp first
    $fullName = trim(implode(' ', array_filter([
        $person->FirstName  ?? '',
        $person->SecondName ?? '',
        $person->ThirdName  ?? '',
        $person->FourthName ?? '',
    ])));

    try {
        $payload = [
            'full_number' => $phone,
            'message'     => "اهلا بك يا {$fullName}\n"
                           . "الرقم الخاص بك: {$personId}\n"
                           . "الرقم: {$plainPassword}\n"
                           . "يرجى تغيير الرقم عند أول تسجيل دخول.\n"
                           . "مرحبا بك في الكشافة.",
        ];
        $fake = Request::create('/whatsapp/send', 'POST', $payload);
        app(\App\Http\Controllers\WhatsAppBridgeController::class)->send($fake);
    } catch (\Throwable $e) {
        Log::error('WhatsApp send failed; aborting update', ['person_id' => $personId, 'error' => $e->getMessage()]);
        return back()->with('error', 'Failed to send WhatsApp. Password was NOT updated.');
    }

    // Hash + upsert
    DB::table('PersonSystemPassword')->updateOrInsert(
        ['PersonID' => $personId],
        ['Password' => Hash::make($plainPassword), 'updated_at' => now()]
    );

    return back()->with('success', 'Temporary password sent on WhatsApp and updated in the system.');
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