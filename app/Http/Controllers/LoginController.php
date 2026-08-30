<?php

namespace App\Http\Controllers;

use App\Domain\Authz\PermissionService;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    private const DUMMY_HASH = '$2y$10$usesomesillystringfore7hnbRJHxMlgFlAcQFRY3NmR7N2146wo';

    public function show()
    {
        return view('login');
    }

    public function login(LoginRequest $request)
    {
        $personId = (int) $request->validated()['person_id'];
        $plainPassword = (string) $request->validated()['person_password'];

        $user = User::query()->find($personId);
        $hashed = $user?->password?->Password ?: self::DUMMY_HASH;
        $passwordOk = Hash::check($plainPassword, $hashed);
        $staffOk = app(PermissionService::class)->isStaff($user);

        if (! $user || ! $user->password?->Password || ! $passwordOk || ! $staffOk) {
            return $this->invalidLogin($request);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    protected function invalidLogin(Request $request)
    {
        return back()
            ->withErrors([
                'login' => __('These credentials do not match our records.'),
            ])
            ->withInput($request->only('person_id'));
    }
}
