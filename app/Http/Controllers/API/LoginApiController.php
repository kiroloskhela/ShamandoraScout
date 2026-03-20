<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\RefreshToken;
use \Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\DB;

class LoginApiController extends Controller
{
    // POST /api/login

/**
 * @OA\Tag(
 *   name="Auth",
 *   description="Authentication endpoints (login/logout)"
 * )
 *
 * @OA\Post(
 *   path="/api/login",
 *   operationId="apiLogin",
 *   tags={"Auth"},
 *   summary="Login and get access + refresh tokens",
 *   description="Validates user credentials then returns a short-lived access token (1 hour) and a long-lived refresh token (30 days).",
 *
 *   @OA\RequestBody(
 *     required=true,
 *     @OA\JsonContent(
 *       type="object",
 *       required={"id","password"},
 *       @OA\Property(property="id", type="integer", example=55),
 *       @OA\Property(property="password", type="string", example="secret123")
 *     )
 *   ),
 *
 *   @OA\Response(
 *     response=200,
 *     description="Logged in",
 *     @OA\JsonContent(
 *       type="object",
 *       @OA\Property(property="access_token", type="string", example="1|xxxxxxxxxxxxxxxxxxxxxxxx"),
 *       @OA\Property(property="token_type", type="string", example="Bearer"),
 *       @OA\Property(property="expires_in_sec", type="integer", example=3600),
 *       @OA\Property(property="refresh_token", type="string", example="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx")
 *     )
 *   ),
 *
 *   @OA\Response(
 *     response=401,
 *     description="Invalid credentials",
 *     @OA\JsonContent(
 *       type="object",
 *       @OA\Property(property="error", type="string", example="Invalid credentials")
 *     )
 *   ),
 *
 *   @OA\Response(
 *     response=422,
 *     description="Validation error",
 *     @OA\JsonContent(type="object")
 *   )
 * )
 *
 * @OA\Post(
 *   path="/api/logout",
 *   operationId="apiLogout",
 *   tags={"Auth"},
 *   summary="Logout (revoke current token)",
 *   description="Revokes the current access token (Sanctum) and revokes active refresh tokens for the user.",
 *   security={{"bearerAuth":{}}},
 *
 *   @OA\Response(
 *     response=200,
 *     description="Logged out",
 *     @OA\JsonContent(
 *       type="object",
 *       @OA\Property(property="message", type="string", example="Logged out")
 *     )
 *   ),
 *
 *   @OA\Response(
 *     response=401,
 *     description="Unauthorized",
 *     @OA\JsonContent(type="object")
 *   )
 * )
 */





    public function apiLogin(Request $request)
{
    $request->validate([
        'id'       => 'required|integer',
        'password' => 'required',
        'fcm_token'  => 'nullable|string',
        'platform'   => 'nullable|string|in:android,ios,web',
    ]);

    $user = User::find($request->id);

    $hashedPassword = optional($user->password)->Password;
    if (! $user || ! Hash::check($request->password, $hashedPassword)) {
        return response()->json(['error' => 'Invalid credentials'], 401);
    }

    // 1) short-lived access token (1 hour)
    $accessToken = $user->createToken('api-token', ['*'], now()->addHours(1))->plainTextToken;

    // 2) long-lived refresh token (30 days)
    $plainRefresh = str::random(64);
    RefreshToken::create([
        'user_id'    => $user->PersonID,
        'token_hash' => hash('sha256', $plainRefresh),
        'expires_at' => now()->addDays(30),
        'ip'         => $request->ip(),
        'user_agent' => substr((string) $request->userAgent(), 0, 1000),
    ]);


if ($request->filled('fcm_token')) {

    DB::table('devices')->updateOrInsert(
        [
            'PersonID' => $user->PersonID,
            'platform' => $request->platform,
        ],
        [
            'fcm_token' => $request->fcm_token,
            'updated_at'=> now(),
            'created_at'=> now(),
        ]
    );

}

    return response()->json([
        'access_token'   => $accessToken,
        'token_type'     => 'Bearer',
        'expires_in_sec' => 3600,
        'refresh_token'  => $plainRefresh,
    ]);


    
}

public function apiLogout(Request $request)
{
    $token = $request->user()->currentAccessToken();

    if ($token instanceof PersonalAccessToken) {
        // API token auth: revoke this access token
        $token->delete();
    } else {
        // SPA (cookie) auth: end the session
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    // (Optional) revoke all active refresh tokens for this user
    RefreshToken::where('user_id', $request->user()->PersonID)
        ->whereNull('revoked_at')
        ->update(['revoked_at' => now()]);

    return response()->json(['message' => 'Logged out']);
}

  

// public function logout(Request $request)
// {
//     $pat = PersonalAccessToken::findToken($request->bearerToken());
//     $pat?->delete();

//     return response()->json(['message' => 'Logged out']);
// }

}