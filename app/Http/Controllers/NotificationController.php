<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Services\FcmService;
use Exception;

class NotificationController extends Controller
{
    public function index()
    {
        $users = DB::table('PersonInformation')
            ->select('PersonID', 'FirstName', 'SecondName', 'ThirdName')
            ->get();

        return view('notifications.create', compact('users'));
    }

    public function create()
    {
        return $this->index();
    }

    public function send(Request $request)
    {
        // ✅ Validation
        $request->validate([
            'person_id' => 'required',
            'title'     => 'required|string|max:255',
            'body'      => 'required|string',
        ]);

        $personId = $request->person_id;

        // ✅ Get user tokens
        $tokens = DB::table('devices')
            ->where('PersonID', $personId)
            ->whereNotNull('fcmtoken')
            ->pluck('fcmtoken')
            ->unique()
            ->toArray();

        // ❌ No devices
        if (empty($tokens)) {
            return back()->with('error', 'لا يوجد جهاز مرتبط بهذا المستخدم');
        }

        try {

            // ✅ Send notification
            $fcm = new FcmService();

            $fcm->sendToMultiple(
                $tokens,
                $request->title,
                $request->body
            );

            return back()->with('success', 'تم إرسال الإشعار بنجاح');

        } catch (\Exception $e) {

            return back()->with(
                'error',
                'حدث خطأ أثناء إرسال الإشعار: ' . $e->getMessage()
            );
        }
    }

   public static function sendToRoles(array $roles, string $title, string $body): bool
{
    try {

        // ✅ Get all users with these roles
        $personIds = DB::table('users')
            ->whereIn('Role', $roles)
            ->pluck('PersonID')
            ->toArray();

        if (empty($personIds)) {
            return false;
        }

        // ✅ Get all FCM tokens
        $tokens = DB::table('devices')
            ->whereIn('PersonID', $personIds)
            ->whereNotNull('fcmtoken')
            ->pluck('fcmtoken')
            ->unique()
            ->values()
            ->toArray();

        if (empty($tokens)) {
            return false;
        }

        // ✅ Send notification
        $fcm = new FcmService();

        $fcm->sendToMultiple(
            $tokens,
            $title,
            $body
        );

        return true;

    } catch (Exception $e) {

        Log::error('Notification Error', [
            'message' => $e->getMessage()
        ]);

        return false;
    }
    }

    public static function sendToUserId(int $personId, string $title, string $body): bool
    {
        try {

            // ✅ Get FCM tokens for this user
            $tokens = DB::table('devices')
                ->where('PersonID', $personId)
                ->whereNotNull('fcmtoken')
                ->pluck('fcmtoken')
                ->unique()
                ->values()
                ->toArray();

            // ❌ No devices found
            if (empty($tokens)) {
                return false;
            }

            // ✅ Send notification
            $fcm = new FcmService();

            $fcm->sendToMultiple(
                $tokens,
                $title,
                $body
            );

            return true;

        } catch (Exception $e) {

            Log::error('Notification Error (sendToUserId)', [
                'person_id' => $personId,
                'message' => $e->getMessage()
            ]);

            return false;
        }
    }

    public static function sendToIds(array|int $ids, string $title, string $body): bool
{
    try {

        $ids = (array) $ids;

        $tokens = DB::table('devices')
            ->whereIn('PersonID', $ids)
            ->whereNotNull('fcmtoken')
            ->pluck('fcmtoken')
            ->unique()
            ->values()
            ->toArray();

        if (empty($tokens)) {
            return false;
        }

        (new FcmService())->sendToMultiple($tokens, $title, $body);

        return true;

    } catch (Exception $e) {
        Log::error('Notification Error (sendToIds)', [
            'ids' => $ids,
            'message' => $e->getMessage()
        ]);

        return false;
    }
}


}