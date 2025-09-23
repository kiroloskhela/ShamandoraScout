<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
class PersonProfileController extends Controller
{
    // Show profile page
    public function show()
    {
        $user = Auth::user();
        return view('profile', compact('user'));
    }

    // Show edit form
    public function edit()
    {
        $user = Auth::user();
        return view('profile-edit', compact('user'));
    }

    // Update profile data
    public function update(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'FirstName' => 'required',
            'SecondName' => 'required',
            'ThirdName' => 'required',
            'FourthName' => 'required',
            'ScoutJoiningYear' => 'required|integer',
            'PersonPersonalMobileNumber' => 'required',
        ]);
        // Update user info except ShamandoraCode and password
        DB::table('PersonInformation')
            ->where('PersonID', $user->PersonID)
            ->update([
                'FirstName' => $validated['FirstName'],
                'SecondName' => $validated['SecondName'],
                'ThirdName' => $validated['ThirdName'],
                'FourthName' => $validated['FourthName'],
                'ScoutJoiningYear' => $validated['ScoutJoiningYear'],
            ]);
        DB::table('PersonPhoneNumbers')
            ->where('PersonID', $user->PersonID)
            ->update(['PersonPersonalMobileNumber' => $validated['PersonPersonalMobileNumber']]);
        return Redirect::route('profile.show')->with('success', 'Profile updated successfully.');
    }

    // New method for updating password only
public function updatePassword(Request $request)
{
    $request->validate([
        'password' => 'required|min:6',
        // 'confirmed' if you also add password_confirmation in the form
    ]);

    $personId = Auth::user()->getAuthIdentifier(); // ensures we use the auth PK

    DB::table('PersonSystemPassword')->updateOrInsert(
        ['PersonID' => $personId],
        ['Password' => Hash::make($request->input('password'))]
    );

    return Redirect::route('profile.show')->with('success', 'Password updated successfully.');
}

}