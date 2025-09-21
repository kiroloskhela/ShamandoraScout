<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

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
            'password' => 'nullable|min:6',
        ]);
        // Update user info except ShamandoraCode
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
        if (!empty($validated['password'])) {
            DB::table('PersonSystemPassword')
                ->where('PersonID', $user->PersonID)
                ->update(['Password' => $validated['password']]);
        }
        return Redirect::route('profile.show')->with('success', 'Profile updated successfully.');
    }
}
