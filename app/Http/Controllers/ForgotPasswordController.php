<?php

namespace App\Http\Controllers;

use App\Domain\Auth\PasswordResetLinkService;
use App\Services\BrevoService;
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
     * Verify phone + DOB (+ optional NID), issue a reset link, email it.
     * WhatsApp gets the same URL (best-effort) so the channel can be primary later.
     * Password is NOT changed until the user submits the reset form.
     */
    public function handle(Request $request, PasswordResetLinkService $resets, BrevoService $brevo)
    {
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
            if ($nid) {
                return back()->with('error', 'البيانات لا تطابق أي مستخدم. يرجى التحقق من الرقم القومي.')
                    ->withInput();
            }

            return back()->with('error', 'لم يتم العثور على مستخدم يطابق رقم الهاتف وتاريخ الميلاد.')
                ->withInput();
        }

        if ($matches->count() > 1 && !$nid) {
            return back()
                ->with('need_raqam_qawmy', true)
                ->with('info', 'يرجى إدخال الرقم القومي لتأكيد الهوية.')
                ->withInput();
        }

        $person = $matches->first();
        $personId = (int) $person->PersonID;
        $email = trim((string) ($person->PersonalEmail ?? ''));

        if ($email === '') {
            return back()->with('error', 'لا يوجد بريد إلكتروني مسجل لهذا المستخدم.')
                ->withInput();
        }

        $fullName = trim(implode(' ', array_filter([
            $person->FirstName ?? '',
            $person->SecondName ?? '',
            $person->ThirdName ?? '',
            $person->FourthName ?? '',
        ])));

        $resetUrl = $resets->issueResetUrl($email);
        $logoUrl = url('/img/shamandora.png');
        $expireMinutes = $resets->expireMinutes();

        try {
            $brevo->sendPasswordResetLinkBilingual(
                $email,
                $fullName,
                (string) $personId,
                $resetUrl,
                $logoUrl,
                $expireMinutes
            );
        } catch (\Throwable $e) {
            Log::error('Password reset email failed', [
                'person_id' => $personId,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'فشل إرسال رابط إعادة التعيين إلى البريد الإلكتروني. لم يتم تغيير كلمة السر.')
                ->withInput();
        }

        // Best-effort WhatsApp with the same URL (primary channel later).
        try {
            $payload = [
                'full_number' => $phone,
                'message' => "اهلا بك يا {$fullName}\n"
                    . "الرقم الخاص بك: {$personId}\n"
                    . "لإعادة تعيين كلمة السر، افتح الرابط التالي (صالح لمدة {$expireMinutes} دقيقة):\n"
                    . "{$resetUrl}\n"
                    . "مرحبا بك في الكشافة.",
            ];
            $fake = Request::create('/whatsapp/send', 'POST', $payload);
            app(WhatsAppBridgeController::class)->send($fake);
        } catch (\Throwable $e) {
            Log::warning('Password reset WhatsApp notify failed (email already sent)', [
                'person_id' => $personId,
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'تم إرسال رابط إعادة تعيين كلمة السر إلى بريدك الإلكتروني.');
    }

    public function showResetForm(Request $request, string $token, PasswordResetLinkService $resets)
    {
        $email = strtolower(trim((string) $request->query('email', '')));

        if ($email === '' || !$resets->tokenIsValid($email, $token)) {
            return redirect()->route('forgot-password.form')
                ->with('error', 'رابط إعادة التعيين غير صالح أو منتهي الصلاحية. اطلب رابطاً جديداً.');
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
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.min' => 'كلمة السر يجب ألا تقل عن 8 أحرف.',
            'password.confirmed' => 'تأكيد كلمة السر غير متطابق.',
        ]);

        $email = strtolower(trim($data['email']));

        if (!$resets->tokenIsValid($email, $data['token'])) {
            return redirect()->route('forgot-password.form')
                ->with('error', 'رابط إعادة التعيين غير صالح أو منتهي الصلاحية. اطلب رابطاً جديداً.');
        }

        $personId = DB::table('PersonInformation')
            ->whereRaw('LOWER(TRIM(PersonalEmail)) = ?', [$email])
            ->value('PersonID');

        if (!$personId) {
            return redirect()->route('forgot-password.form')
                ->with('error', 'تعذر العثور على الحساب المرتبط بهذا البريد.');
        }

        DB::table('PersonSystemPassword')->updateOrInsert(
            ['PersonID' => $personId],
            ['Password' => Hash::make($data['password']), 'updated_at' => now()]
        );

        $resets->consumeToken($email);

        return redirect('/login-auth')
            ->with('success', 'تم تعيين كلمة السر الجديدة. يمكنك تسجيل الدخول الآن.');
    }
}
