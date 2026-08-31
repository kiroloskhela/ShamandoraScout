<?php

namespace App\Http\Controllers;

use App\Domain\Auth\PasswordResetLinkService;
use App\Domain\Auth\TokenSessionService;
use App\Jobs\SendPasswordResetLinkMail;
use App\Services\WhatsAppBridgeClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{
    public function showForm()
    {
        return view('forgot-password.form');
    }

    /**
     * Verify phone + DOB (+ optional NID), issue a reset link, send via WhatsApp
     * (and email when available). Password is NOT changed until the reset form.
     */
    public function handle(
        Request $request,
        PasswordResetLinkService $resets,
        WhatsAppBridgeClient $whatsapp
    ) {
        $baseRules = [
            'phone' => ['required', 'regex:/^0\d{10}$/'],
            'dob' => ['required', 'date_format:Y-m-d'],
        ];

        if ($request->filled('raqam_qawmy')) {
            $baseRules['raqam_qawmy'] = ['required', 'regex:/^\d{14}$/'];
        }

        $request->validate($baseRules, [
            'phone.regex' => 'Phone must be 11 digits and start with 0 (e.g. 01000485402).',
            'dob.date_format' => 'DOB must be YYYY-MM-DD (e.g., 2001-03-29).',
            'raqam_qawmy.regex' => 'RaqamQawmy must be 14 digits.',
        ]);

        $phone = preg_replace('/\D+/', '', (string) $request->input('phone'));
        $dob = (string) $request->input('dob');
        $nid = $request->filled('raqam_qawmy')
            ? preg_replace('/\D+/', '', $request->input('raqam_qawmy'))
            : null;

        $q = DB::table('PersonInformation as pi')
            ->join('PersonPhoneNumbers as ppn', 'ppn.PersonID', '=', 'pi.PersonID')
            ->whereRaw('TRIM(ppn.PersonPersonalMobileNumber) = ?', [$phone])
            ->where('pi.DateOfBirth', '=', $dob)
            ->select(
                'pi.PersonID',
                'pi.FirstName',
                'pi.SecondName',
                'pi.ThirdName',
                'pi.FourthName',
                'pi.PersonalEmail'
            );

        if ($nid) {
            $q->where('pi.RaqamQawmy', '=', $nid);
        }

        $matches = $q->limit(2)->get();

        if ($matches->isEmpty()) {
            // Same generic success as a real send — do not reveal whether the phone exists.
            return back()->with('success', __('Password reset link sent via WhatsApp.'));
        }

        if ($matches->count() > 1 && ! $nid) {
            return back()
                ->with('need_raqam_qawmy', true)
                ->with('info', __('Please enter your national ID to confirm identity.'))
                ->withInput();
        }

        $person = $matches->first();
        $personId = (int) $person->PersonID;
        $email = trim((string) ($person->PersonalEmail ?? ''));

        $fullName = trim(implode(' ', array_filter([
            $person->FirstName ?? '',
            $person->SecondName ?? '',
            $person->ThirdName ?? '',
            $person->FourthName ?? '',
        ])));

        $tokenKey = $resets->tokenKeyForPerson($personId, $email !== '' ? $email : null);
        $resetUrl = $resets->issueResetUrl($tokenKey);
        $expireMinutes = $resets->expireMinutes();

        $waMessage = __('Hello :name ♡', ['name' => $fullName])."\n"
            .__('To reset your password, open the following link within :minutes minutes:', ['minutes' => $expireMinutes])."\n"
            ."{$resetUrl}\n"
            .__('If you did not request this, ignore this message.');

        try {
            $whatsapp->sendText($phone, $waMessage);
        } catch (\Throwable $e) {
            Log::error('Password reset WhatsApp send failed', [
                'person_id' => $personId,
                'error' => $e->getMessage(),
            ]);

            if (app()->environment('local')) {
                return back()->with(
                    'error',
                    __('WhatsApp send failed. For local testing use this link: ').$resetUrl
                )->withInput();
            }

            // Production: same generic success so WA outages do not confirm the account.
            return back()->with('success', __('Password reset link sent via WhatsApp.'));
        }

        if ($email !== '') {
            $logoUrl = config('services.brevo.logo_url', 'https://shamandorascout.com/img/shamandora.png');
            try {
                SendPasswordResetLinkMail::dispatch(
                    $email,
                    $fullName,
                    (string) $personId,
                    $resetUrl,
                    $logoUrl,
                    $expireMinutes
                );
            } catch (\Throwable $e) {
                Log::warning('Password reset email dispatch failed after WhatsApp OK', [
                    'person_id' => $personId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return back()->with('success', __('Password reset link sent via WhatsApp.'));
    }

    public function showResetForm(Request $request, string $token, PasswordResetLinkService $resets)
    {
        $email = strtolower(trim((string) $request->query('email', '')));

        if ($email === '' || ! $resets->tokenIsValid($email, $token)) {
            return redirect()->route('forgot-password.form')
                ->with('error', __('Reset link is invalid or expired. Request a new link.'));
        }

        return view('forgot-password.reset', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function reset(Request $request, PasswordResetLinkService $resets)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.min' => __('Password must be at least 8 characters.'),
            'password.confirmed' => __('Password confirmation does not match.'),
        ]);

        $email = strtolower(trim($data['email']));

        if (! $resets->tokenIsValid($email, $data['token'])) {
            return redirect()->route('forgot-password.form')
                ->with('error', __('Reset link is invalid or expired. Request a new link.'));
        }

        $personId = null;
        if (preg_match('/^person-(\d+)@password-reset\.local$/', $email, $m)) {
            $personId = (int) $m[1];
        } else {
            $personId = DB::table('PersonInformation')
                ->whereRaw('LOWER(TRIM(PersonalEmail)) = ?', [$email])
                ->value('PersonID');
        }

        if (! $personId) {
            return redirect()->route('forgot-password.form')
                ->with('error', __('Could not find the account linked to this reset link.'));
        }

        DB::transaction(function () use ($personId, $data) {
            DB::table('PersonSystemPassword')->updateOrInsert(
                ['PersonID' => $personId],
                ['Password' => Hash::make($data['password']), 'updated_at' => now()]
            );
            app(TokenSessionService::class)->revokeAllForUser((int) $personId);
        });

        $resets->consumeToken($email);

        return redirect('/login-auth')
            ->with('success', __('New password set. You can log in now.'));
    }
}
