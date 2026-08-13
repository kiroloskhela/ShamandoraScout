<?php

namespace App\Http\Controllers\API;

use App\Domain\Auth\TokenSessionService;
use App\Domain\Authz\PermissionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TokenApiController extends Controller
{
    public function refresh(Request $request, TokenSessionService $sessions, PermissionService $permissions)
    {
        $request->validate(['refresh_token' => 'required']);

        $tokens = $sessions->refresh((string) $request->input('refresh_token'));
        if (! $tokens) {
            return response()->json(['error' => 'Invalid or expired refresh token'], 401);
        }

        $user = $tokens['user'];

        return response()->json([
            'ok' => true,
            'access_token' => $tokens['access_token'],
            'token_type' => 'Bearer',
            'expires_in_sec' => $tokens['expires_in_sec'],
            'refresh_token' => $tokens['refresh_token'],
            'role_names' => $user->role()->pluck('RoleName')->values(),
            'permissions' => $permissions->clientKeysForUser($user),
        ]);
    }
}
