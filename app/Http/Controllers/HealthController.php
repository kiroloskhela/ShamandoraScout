<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class HealthController extends Controller
{
    /**
     * Lightweight readiness probe for load balancers / uptime monitors.
     * Does not require auth. Avoids leaking internals beyond ok/status.
     */
    public function __invoke(): JsonResponse
    {
        $checks = [
            'app' => true,
            'database' => false,
            'failed_jobs' => null,
        ];

        try {
            DB::select('select 1');
            $checks['database'] = true;
        } catch (Throwable) {
            $checks['database'] = false;
        }

        if (Schema::hasTable('failed_jobs')) {
            try {
                $checks['failed_jobs'] = (int) DB::table('failed_jobs')->count();
            } catch (Throwable) {
                $checks['failed_jobs'] = null;
            }
        }

        $ok = $checks['app'] && $checks['database'];

        return response()->json([
            'ok' => $ok,
            'status' => $ok ? 'healthy' : 'degraded',
            'checks' => $checks,
            'release' => env('SENTRY_RELEASE') ?: null,
            'time' => now()->toIso8601String(),
        ], $ok ? 200 : 503);
    }
}
