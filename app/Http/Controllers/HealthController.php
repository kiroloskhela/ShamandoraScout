<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class HealthController extends Controller
{
    /**
     * Public readiness probe. Detailed ops fields require HEALTH_TOKEN.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $checks = [
            'app' => true,
            'database' => false,
        ];

        try {
            DB::select('select 1');
            $checks['database'] = true;
        } catch (Throwable) {
            $checks['database'] = false;
        }

        $ok = $checks['app'] && $checks['database'];

        $payload = [
            'ok' => $ok,
            'status' => $ok ? 'healthy' : 'degraded',
            'checks' => $checks,
            'time' => now()->toIso8601String(),
        ];

        if ($this->wantsDetails($request)) {
            $payload['checks']['failed_jobs'] = null;

            if (Schema::hasTable('failed_jobs')) {
                try {
                    $payload['checks']['failed_jobs'] = (int) DB::table('failed_jobs')->count();
                } catch (Throwable) {
                    $payload['checks']['failed_jobs'] = null;
                }
            }

            // Prefer config() — env() is null after `php artisan config:cache`.
            $release = config('sentry.release') ?: config('app.release');
            $payload['release'] = $release ?: null;
            $payload['log_channel'] = config('logging.default');
        }

        return response()->json($payload, $ok ? 200 : 503);
    }

    private function wantsDetails(Request $request): bool
    {
        $token = (string) config('app.health_token', '');

        if ($token === '') {
            return false;
        }

        $provided = (string) $request->query('token', '');

        return $provided !== '' && hash_equals($token, $provided);
    }
}
