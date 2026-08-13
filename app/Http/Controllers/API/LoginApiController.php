<?php

namespace App\Http\Controllers\API;

use App\Domain\Auth\TokenSessionService;
use App\Domain\Authz\PermissionService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\PersonalAccessToken;



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
        'id'        => 'required|integer',
        'password'  => 'required',
        'fcmtoken'  => 'nullable|string',
        'platform'  => 'nullable|string|in:android,ios,web',
    ]);

    $limitKey = 'api-login-id:'.$request->integer('id');
    if (RateLimiter::tooManyAttempts($limitKey, 5)) {
        return response()->json([
            'ok' => false,
            'message' => 'Invalid credentials',
        ], 401);
    }

    $user = User::find($request->id);
    $permissions = app(PermissionService::class);
    $hashedPassword = $user?->password?->Password
        ?: '$2y$10$usesomesillystringfore7hnbRJHxMlgFlAcQFRY3NmR7N2146wo';

    $passwordOk = Hash::check($request->password, $hashedPassword);
    if (! $user || ! $user->password?->Password || ! $passwordOk || ! $permissions->hasAppAccess($user)) {
        RateLimiter::hit($limitKey, 60);

        return response()->json([
            'ok' => false,
            'message' => 'Invalid credentials',
        ], 401);
    }

    RateLimiter::clear($limitKey);

    $tokens = app(TokenSessionService::class)->issue($user, $request);

    if ($request->filled('fcmtoken')) {
        DB::table('devices')->updateOrInsert(
            [
                'PersonID' => $user->PersonID,
                'platform' => $request->platform,
            ],
            [
                'fcmtoken'   => $request->fcmtoken,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    return response()->json([
        'ok' => true,
        'message' => 'Login successful',
        'access_token' => $tokens['access_token'],
        'token_type' => 'Bearer',
        'expires_in_sec' => $tokens['expires_in_sec'],
        'refresh_token' => $tokens['refresh_token'],
        'role_names' => $user->role()->pluck('RoleName')->values(),
        'permissions' => $permissions->clientKeysForUser($user),
    ]);
}

public function apiLogout(Request $request)
{
    $token = $request->user()->currentAccessToken();

    if ($token instanceof PersonalAccessToken) {
        app(TokenSessionService::class)->logoutCurrent($request->user(), $token);
    } else {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        app(TokenSessionService::class)->revokeAllForUser((int) $request->user()->PersonID);
    }

    return response()->json(['message' => 'Logged out']);
}

  

// public function logout(Request $request)
// {
//     $pat = PersonalAccessToken::findToken($request->bearerToken());
//     $pat?->delete();

//     return response()->json(['message' => 'Logged out']);
// }

}