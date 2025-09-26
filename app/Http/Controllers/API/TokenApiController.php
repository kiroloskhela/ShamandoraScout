<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RefreshToken;

class TokenApiController extends Controller
{
    // POST /api/refresh
    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        $hashed = hash('sha256', $request->refresh_token);

        // find valid refresh
        $stored = RefreshToken::where('token', $hashed)
            ->where('expires_at', '>', now())
            ->first();

        if (! $stored) {
            return response()->json(['error' => 'Invalid or expired refresh token'], 401);
        }

        $user = $stored->user;

        // ROTATE: delete the used refresh token
        $stored->delete();

        // === Hard-coded expiry times ===
        $accessTokenMinutes = 60; // 1 hour
        $refreshTokenDays   = 30; // 30 days

        // Issue new access token
        $new = $user->createToken('api-token');
        $plainAccess = $new->plainTextToken;
        $accessModel = $new->accessToken;
        $accessExpiry = now()->addMinutes($accessTokenMinutes);
        $accessModel->expires_at = $accessExpiry;
        $accessModel->save();

        // Issue new refresh token
        $rawRefresh = base64_encode(random_bytes(64));
        $hashedNew  = hash('sha256', $rawRefresh);
        $refreshExpiry = now()->addDays($refreshTokenDays);

        RefreshToken::create([
            'user_id'    => $user->id,
            'token'      => $hashedNew,
            'expires_at' => $refreshExpiry,
        ]);

        return response()->json([
            'access_token'             => $plainAccess,
            'access_token_expires_at'  => $accessExpiry->toIso8601String(),
            'refresh_token'            => $rawRefresh,
            'refresh_token_expires_at' => $refreshExpiry->toIso8601String(),
            'token_type'               => 'Bearer',
        ]);
    }
}