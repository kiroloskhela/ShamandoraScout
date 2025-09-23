<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Authenticatable; // <-- use the interface

class LoginController extends Controller
{
    /**
     * API Login: Accepts person_id and person_password, returns JSON success/failure
     */
 
    public function show()
    {
        return view('login');
    }

    public function login(LoginRequest $request)
    {
        $personId = (string) $request->input('person_id');
        $plain    = (string) $request->input('person_password');

        // 1) Fetch user row
        $user = DB::table('PersonInformation')
            ->where('PersonID', $personId)
            ->first();

        if (!$user) {
            return response('No User Found!', 404);
        }

        // 2) Fetch password row
        $pwdRow = DB::table('PersonSystemPassword')
            ->where('PersonID', $personId)
            ->first();

        if (!$pwdRow) {
            return response('Wrong Password', 422);
        }

        // 3) Secure hash comparison
        if (!\Illuminate\Support\Facades\Hash::check($plain, $pwdRow->Password)) {
            return response('Wrong Password', 422);
        }

        // 4) Build a GenericUser with an `id` key (required by Auth)
        $userData = (array) $user;
        $userData['id'] = $user->PersonID;

        // 5) Log in and regenerate session
        $genericUser = new GenericUser($userData);
        Auth::login($genericUser);
        $request->session()->regenerate();

        return method_exists($this, 'authenticated')
            ? $this->authenticated($request, $genericUser)
            : redirect()->intended('/');
    }

    // IMPORTANT: type-hint the interface, not App\Models\User
    protected function authenticated(Request $request, Authenticatable $user)
    {
        return redirect()->intended();
    }
}