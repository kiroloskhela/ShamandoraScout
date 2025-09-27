<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\RefreshToken;

class TokenApiController extends Controller
{
    // POST /api/refresh
    public function refresh(Request $request)
    {
        $request->validate(['refresh_token' => 'required']);

        $hash = hash('sha256', $request->input('refresh_token'));

        /** @var RefreshToken|null $old */
        $old = RefreshToken::where('token_hash', $hash)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $old) {
            return response()->json(['error' => 'Invalid or expired refresh token'], 401);
        }

        $user = $old->user;

        // issue short-lived access token (match your TTL)
        $newAccess = $user->createToken('api-token', ['*'], now()->addMinutes(60))->plainTextToken;

        // rotate refresh token
        $newPlain = Str::random(64);
        $newHash  = hash('sha256', $newPlain);

        $new = RefreshToken::create([
            'user_id'    => $user->PersonID,
            'token_hash' => $newHash,
            'expires_at' => now()->addDays(30),
            'ip'         => $request->ip(),
            'user_agent' => substr((string)$request->userAgent(), 0, 1000),
        ]);

        // revoke old + link
        $old->update([
            'revoked_at'     => now(),
            'replaced_by_id' => $new->id,
        ]);

        return response()->json([
            'access_token'   => $newAccess,
            'token_type'     => 'Bearer',
            'expires_in_sec' => 60 * 60, // or 60 if you're testing 1-minute tokens
            'refresh_token'  => $newPlain,
        ]);
    }
}