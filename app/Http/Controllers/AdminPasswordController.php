<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\WhatsAppBridgeController;
use Illuminate\Http\Request as HttpRequest; 
use Illuminate\Support\Facades\Log;
class AdminPasswordController extends Controller
{
    // List all users for password management
    public function index()
    {
        $users = DB::table('PersonInformation')
            ->leftJoin('PersonSystemPassword', 'PersonInformation.PersonID', '=', 'PersonSystemPassword.PersonID')
            ->select('PersonInformation.*', 'PersonSystemPassword.Password')
            ->get();
        return view('admin.passwords-index', compact('users'));
    }

    // Show edit form for a user's password
    public function edit($id)
    {
        $user = DB::table('PersonInformation')->where('PersonID', $id)->first();
        return view('admin.passwords-edit', compact('user'));
    }

    // Update a user's password
public function update(HttpRequest $request, $id)
{
    $request->validate([
        'password' => 'required|min:6',
    ]);

    $plain = (string) $request->input('password');

    // 1) Update (or create) the hashed password
    DB::table('PersonSystemPassword')->updateOrInsert(
        ['PersonID' => $id],
        ['Password' => Hash::make($plain)]
    );

    // 2) Get phone number by PersonID
    $phone = DB::table('PersonPhoneNumbers')
        ->where('PersonID', $id)
        ->value('PersonPersonalMobileNumber');

    // 3) Send WhatsApp using your existing sendWithHeader
    if ($phone) {
        try {
            // Create an internal request payload for sendWithHeader
            $payload = [
                'full_number' => $phone,
                'message'     => "Your New Password Is: {$plain}",
            ];

            // Build a fake POST Request object and call the controller directly
            $fake = HttpRequest::create('/whatsapp/send-with-header', 'POST', $payload);

            app(WhatsAppBridgeController::class)->sendWithHeader($fake);
            // We ignore the returned RedirectResponse; we’ll always go back to admin list
        } catch (\Throwable $e) {
            Log::error('Failed to send WA new password via sendWithHeader', [
                'person_id' => $id,
                'error'     => $e->getMessage(),
            ]);
        }
    } else {
        Log::warning('No phone found for WA new password', ['person_id' => $id]);
    }

    return Redirect::route('admin.passwords')->with('success', 'Password updated. WhatsApp sent if phone exists.');
}
}