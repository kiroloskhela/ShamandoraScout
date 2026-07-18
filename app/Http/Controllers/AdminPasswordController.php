<?php

namespace App\Http\Controllers;

use App\Support\LikeSearch;
use Illuminate\Http\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class AdminPasswordController extends Controller
{
    public function index(Request $request)
    {
        $term = LikeSearch::fromRequest($request);

        $query = DB::table('PersonInformation as pi')
            ->leftJoin('PersonPhoneNumbers as ppn', 'ppn.PersonID', '=', 'pi.PersonID')
            ->select(
                'pi.PersonID',
                'pi.FirstName',
                'pi.SecondName',
                'pi.ThirdName',
                'pi.FourthName',
                'pi.ShamandoraCode',
                'ppn.PersonPersonalMobileNumber',
            )
            ->orderBy('pi.PersonID');

        if ($term !== null) {
            $query->where(function ($sub) use ($term) {
                LikeSearch::applyFlexiblePersonMatch($sub, $term, 'pi', 'ppn');
            });
        }

        $users = $query->paginate(50)->appends($request->query());

        return view('admin.passwords-index', [
            'users' => $users,
            'q' => $term ?? '',
        ]);
    }

    public function edit($id)
    {
        $user = DB::table('PersonInformation')->where('PersonID', $id)->first();

        return view('admin.passwords-edit', compact('user'));
    }

    public function update(HttpRequest $request, $id)
    {
        $request->validate([
            'password' => 'required|min:6',
        ]);

        $plain = (string) $request->input('password');

        DB::table('PersonSystemPassword')->updateOrInsert(
            ['PersonID' => $id],
            ['Password' => Hash::make($plain)]
        );

        $phone = DB::table('PersonPhoneNumbers')
            ->where('PersonID', $id)
            ->value('PersonPersonalMobileNumber');

        if ($phone) {
            try {
                $loginUrl = route('login-auth');
                $message = "تم تغيير كلمة المرور الخاصة بك بواسطة المسؤول.\n\n"
                    ."رقم المستخدم (ID): {$id}\n"
                    ."كلمة المرور الجديدة: {$plain}\n\n"
                    ."سجّل الدخول من هنا:\n{$loginUrl}";

                $payload = [
                    'full_number' => $phone,
                    'message' => $message,
                ];

                $fake = HttpRequest::create('/whatsapp/send-with-header', 'POST', $payload);

                app(WhatsAppBridgeController::class)->sendWithHeader($fake);
            } catch (\Throwable $e) {
                Log::error('Failed to send WA new password via sendWithHeader', [
                    'person_id' => $id,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            Log::warning('No phone found for WA new password', ['person_id' => $id]);
        }

        return Redirect::route('admin.passwords')->with('success', 'تم تحديث كلمة المرور. تم إرسال واتساب إن وُجد رقم.');
    }
}
