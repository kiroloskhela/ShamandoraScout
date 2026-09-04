<?php

namespace App\Http\Controllers;

use App\Domain\Auth\PasswordResetLinkService;
use App\Domain\Auth\TokenSessionService;
use App\Services\WhatsAppBridgeClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class AdminPasswordController extends Controller
{
    public function index(Request $request)
    {
        $users = DB::table('PersonInformation as pi')
            ->leftJoin('PersonPhoneNumbers as ppn', 'ppn.PersonID', '=', 'pi.PersonID')
            ->select(
                'pi.PersonID',
                'pi.FirstName',
                'pi.SecondName',
                'pi.ThirdName',
                'pi.FourthName',
                'pi.ShamandoraCode',
                'ppn.PersonPersonalMobileNumber',
                DB::raw("TRIM(CONCAT_WS(' ', pi.FirstName, pi.SecondName, pi.ThirdName, pi.FourthName)) AS FullName"),
            )
            ->orderBy('pi.PersonID')
            ->get();

        return view('admin.passwords-index', [
            'users' => $users,
        ]);
    }

    public function edit($id)
    {
        $person = DB::table('PersonInformation')->where('PersonID', $id)->first();
        abort_if(! $person, 404);

        return view('admin.passwords-edit', ['user' => $person]);
    }

    public function update(Request $request, $id)
    {
        $person = DB::table('PersonInformation')->where('PersonID', $id)->first();
        abort_if(! $person, 404);

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'max:72', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
        ], [
            'password.min' => __('Password must be at least 8 characters.'),
            'password.max' => __('Password must be at most 72 characters.'),
            'password.regex' => __('Password must include at least one uppercase letter, one lowercase letter, and one number.'),
        ]);

        $plain = (string) $request->input('password');

        DB::transaction(function () use ($id, $plain) {
            DB::table('PersonSystemPassword')->updateOrInsert(
                ['PersonID' => $id],
                ['Password' => Hash::make($plain)]
            );
            app(TokenSessionService::class)->revokeAllForUser((int) $id);
        });

        $phone = DB::table('PersonPhoneNumbers')
            ->where('PersonID', $id)
            ->value('PersonPersonalMobileNumber');

        if ($phone) {
            try {
                $resets = app(PasswordResetLinkService::class);
                $email = trim((string) ($person->PersonalEmail ?? ''));
                $tokenKey = $resets->tokenKeyForPerson((int) $id, $email !== '' ? $email : null);
                $resetUrl = $resets->issueResetUrl($tokenKey);
                $loginUrl = route('login-auth');
                $message = __('Your password was changed by an administrator.')."\n\n"
                    .__('If you did not expect this, set a new password with this link:')."\n{$resetUrl}\n\n"
                    .__('Log in here:')."\n{$loginUrl}";

                app(WhatsAppBridgeClient::class)->sendText((string) $phone, $message);
            } catch (\Throwable $e) {
                Log::error('Failed to send WA password-change notice', [
                    'person_id' => $id,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            Log::warning('No phone found for WA password change notice', ['person_id' => $id]);
        }

        return Redirect::route('admin.passwords.edit', $id)
            ->with('success', __('Password updated successfully.'));
    }
}
