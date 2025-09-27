<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class LoginApiController extends Controller
{
    // POST /api/login
public function apiLogin(Request $request)
{
    $request->validate([
        'id'       => 'required|integer',
        'password' => 'required',
    ]);

    $user = User::find($request->id);

    $hashedPassword = optional($user->password)->Password;
    if (! $user || ! Hash::check($request->password, $hashedPassword)) {
        return response()->json(['error' => 'Invalid credentials'], 401);
    }

    // 1) short-lived access token (1 hour)
    $accessToken = $user->createToken('api-token', ['*'], now()->addMinutes(60))->plainTextToken;

    // 2) long-lived refresh token (30 days)
    $plainRefresh = \Illuminate\Support\Str::random(64);
    \App\Models\RefreshToken::create([
        'user_id'    => $user->PersonID,
        'token_hash' => hash('sha256', $plainRefresh),
        'expires_at' => now()->addDays(30),
        'ip'         => $request->ip(),
        'user_agent' => substr((string) $request->userAgent(), 0, 1000),
    ]);

    return response()->json([
        'access_token'   => $accessToken,
        'token_type'     => 'Bearer',
        'expires_in_sec' => 3600,
        'refresh_token'  => $plainRefresh,
    ]);
}

    
}