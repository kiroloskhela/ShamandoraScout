<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Str;
use App\Models\RefreshToken;
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

                if (! $user || ! Hash::check($request->password, $user->password)) {
                    return response()->json(['error' => 'Invalid credentials'], 401);
                }

                // Create short-lived access token
                $tokenResult = $user->createToken('api-token');
                $plainTextToken = $tokenResult->plainTextToken;

                $token = $tokenResult->accessToken ?? $tokenResult->token;
                $token->expires_at = now()->addHour();
                $token->save();

                // Create refresh token (valid for 7 days)
                $refreshToken = Str::random(64);
                RefreshToken::create([
                    'user_id' => $user->id,
                    'token' => hash('sha256', $refreshToken),
                    'expires_at' => now()->addDays(7),
                ]);

                return response()->json([
                    'access_token' => $plainTextToken,
                    'expires_at'   => $token->expires_at,
                    'refresh_token'=> $refreshToken,
                ]);

        }
    public function refresh(Request $request)
        {
            $request->validate([
                'refresh_token' => 'required',
            ]);

            $hashedToken = hash('sha256', $request->refresh_token);

            $refreshToken = \App\Models\RefreshToken::where('token', $hashedToken)
                ->where('expires_at', '>', now())
                ->first();

            if (! $refreshToken) {
                return response()->json(['error' => 'Invalid or expired refresh token'], 401);
            }

            $user = $refreshToken->user;

            // Create new access token
            $tokenResult = $user->createToken('api-token');
            $plainTextToken = $tokenResult->plainTextToken;

            $token = $tokenResult->accessToken ?? $tokenResult->token;
            $token->expires_at = now()->addHour();
            $token->save();

            return response()->json([
                'access_token' => $plainTextToken,
                'expires_at'   => $token->expires_at,
            ]);
        }

}