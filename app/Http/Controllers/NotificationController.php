<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\FcmService;

class NotificationController extends Controller
{
    public function index()
    {    $users = DB::table('PersonInformation')
        ->select('PersonID', 'FirstName', 'SecondName', 'ThirdName')
        ->get();

    return view("notifications.create", ['users' => $users]);
    }

public function create()
{
    $users = DB::table('PersonInformation')
        ->select('PersonID', 'FirstName', 'SecondName', 'ThirdName')
        ->get();

    return view("notifications.create", ['users' => $users]);
}


public function send(Request $request)
{
    $personId = $request->person_id;

    // 🔍 Get FCM tokens for this person
    $tokens = DB::table('devices')
        ->where('PersonID', $personId) // IMPORTANT: must match PersonID
        ->pluck('fcmtoken')
        ->toArray();

    // ❌ If no token found
    if (empty($tokens)) {
        return redirect()->back()->with('error', 'لا يوجد جهاز لهذا الشخص');
    }

    // 🚀 Send notification
    $fcm = new FcmService();

    $fcm->sendToMultiple($tokens, $request->title, $request->body);

    return redirect()->back()->with('success', 'تم إرسال الإشعار بنجاح');
}
}