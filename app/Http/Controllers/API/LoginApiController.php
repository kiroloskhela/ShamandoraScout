<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LoginApiController extends Controller
{
    // POST /api/login
    public function apiLogin(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'password' => 'required',
        ]);

        $user = \App\Models\User::find($request->id);

        Log::info('Login attempt', [
            'input_id' => $request->id,
            'input_password' => $request->password,
            'user_exists' => $user ? true : false,
            'user_password' => $user ? $user->password : null,
        ]);

        $actualPassword = is_object($user->password) && isset($user->password->Password) ? $user->password->Password : $user->password;
        if (! $user || ! \Illuminate\Support\Facades\Hash::check($request->password, $actualPassword)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
        ]);
    }
}