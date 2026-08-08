<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Authenticatable;

class LoginController extends Controller
{
    public function show()
    {
        return view('login');
    }

    public function login(LoginRequest $request)
    {
        $personId = (string) $request->validated()['person_id'];
        $plainPassword = (string) $request->validated()['person_password'];

        $user = DB::table('PersonInformation')
            ->where('PersonID', $personId)
            ->first();

        if (!$user) {
            return $this->invalidLogin($request);
        }

        $pwdRow = DB::table('PersonSystemPassword')
            ->where('PersonID', $personId)
            ->first();

        if (!$pwdRow || empty($pwdRow->Password)) {
            return $this->invalidLogin($request);
        }

        if (!Hash::check($plainPassword, $pwdRow->Password)) {
            return $this->invalidLogin($request);
        }

        $userData = (array) $user;
        $userData['id'] = $user->PersonID;

        $genericUser = new GenericUser($userData);

        Auth::login($genericUser);
        $request->session()->regenerate();

        return redirect()->intended('/home');
    }

    protected function invalidLogin(Request $request)
    {
        return back()
            ->withErrors([
                'login' => __('These credentials do not match our records.'),
            ])
            ->withInput($request->only('person_id'));
    }

    protected function authenticated(Request $request, Authenticatable $user)
    {
        return redirect()->intended('/home');
    }
}