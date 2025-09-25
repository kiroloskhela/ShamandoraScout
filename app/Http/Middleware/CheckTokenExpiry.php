<?php
namespace App\Http\Middleware;

use Closure;
use Laravel\Sanctum\PersonalAccessToken;

class CheckTokenExpiry
    {
        public function handle($request, Closure $next)
        {
            $accessToken = $request->bearerToken();
            if (! $accessToken) {
              return response()->json(['error' => 'Unauthorized'], 401);
            }

            $tokenModel = PersonalAccessToken::findToken($accessToken);

            if (! $tokenModel) {
                return response()->json(['error' => 'Invalid token'], 401);
            }

            // Check custom expiry
            if ($tokenModel->expires_at && $tokenModel->expires_at->isPast()) {
              return response()->json(['error' => 'Token expired'], 401);
            }

            return $next($request);
        }
    }