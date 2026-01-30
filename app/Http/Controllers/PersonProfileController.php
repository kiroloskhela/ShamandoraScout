<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class PersonProfileController extends Controller
{
    /**
     * Show profile page (display user data + phone + image)
     */
    public function show()
    {
        $user = Auth::user();

        // Phone
        $phone = DB::table('PersonPhoneNumbers')
            ->where('PersonID', $user->PersonID)
            ->value('PersonPersonalMobileNumber');

        // Image row (may be null)
        $personImage = DB::table('PersonImages')
            ->where('PersonID', $user->PersonID)
            ->first();

        return view('profile', compact('user', 'phone', 'personImage'));
    }

    /**
     * Show edit page (profile edit UI)
     * We'll pass the same data so edit page can show current phone + image too.
     */
    public function edit()
    {
        $user = Auth::user();

        $phone = DB::table('PersonPhoneNumbers')
            ->where('PersonID', $user->PersonID)
            ->value('PersonPersonalMobileNumber');

        $personImage = DB::table('PersonImages')
            ->where('PersonID', $user->PersonID)
            ->first();

        return view('profile-edit', compact('user', 'phone', 'personImage'));
    }

    /**
     * Update profile data + phone + photo
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'FirstName' => 'required|string|max:255',
            'SecondName' => 'required|string|max:255',
            'ThirdName' => 'required|string|max:255',
            'FourthName' => 'required|string|max:255',
            'ScoutJoiningYear' => 'required|integer',
            'PersonPersonalMobileNumber' => 'required|string|max:50',

            // photo
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // 1) Update PersonInformation
        DB::table('PersonInformation')
            ->where('PersonID', $user->PersonID)
            ->update([
                'FirstName' => $validated['FirstName'],
                'SecondName' => $validated['SecondName'],
                'ThirdName' => $validated['ThirdName'],
                'FourthName' => $validated['FourthName'],
                'ScoutJoiningYear' => $validated['ScoutJoiningYear'],
            ]);

        // 2) Update phone (insert if missing)
        DB::table('PersonPhoneNumbers')->updateOrInsert(
            ['PersonID' => $user->PersonID],
            ['PersonPersonalMobileNumber' => $validated['PersonPersonalMobileNumber']]
        );

        // 3) Update photo (delete old if exists, then store new)
        if ($request->hasFile('profile_image')) {

            // get old image to delete (if any)
            $old = DB::table('PersonImages')
                ->where('PersonID', $user->PersonID)
                ->first();

            if ($old && !empty($old->PersonSystemImagePath)) {
                Storage::disk('public')->delete($old->PersonSystemImagePath);
            }

            $imagePath = $request->file('profile_image')->store('person_images', 'public');

            DB::table('PersonImages')->updateOrInsert(
                ['PersonID' => $user->PersonID],
                [
                    'PersonSystemImagePath' => $imagePath,
                    // keep thumbnail fields as null for now unless you generate thumbnails
                    'PersonSystemImageThumbnailPath' => $old->PersonSystemImageThumbnailPath ?? null,
                    'ScoutOfficialUniformImagePath' => $old->ScoutOfficialUniformImagePath ?? null,
                    'ScoutOfficialUniformImageThumbnailPath' => $old->ScoutOfficialUniformImageThumbnailPath ?? null,
                ]
            );
        }

        return Redirect::route('profile.show')->with('success', 'Profile updated successfully.');
    }

    /**
     * Change password only
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();

        // IMPORTANT:
        // Use $user->PersonID (same key used across your tables)
        DB::table('PersonSystemPassword')->updateOrInsert(
            ['PersonID' => $user->PersonID],
            ['Password' => Hash::make($request->input('password'))]
        );

        return Redirect::route('profile.show')->with('success', 'Password updated successfully.');
    }
}