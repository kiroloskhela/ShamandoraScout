<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logs every authenticated mutating HTTP request (POST/PUT/PATCH/DELETE)
 * for SuperAdmin audit review.
 */
class LogAuthenticatedMutations
{
    private const MUTATING = ['POST', 'PUT', 'PATCH', 'DELETE'];

    private const SKIP_PREFIXES = [
        'audit-logs',
        '_ignition',
        'livewire',
        'sanctum/csrf-cookie',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$this->shouldLog($request)) {
            return $response;
        }

        $status = $response->getStatusCode();
        $user = Auth::user();
        $routeName = optional($request->route())->getName();
        $path = '/' . ltrim($request->path(), '/');
        $method = strtoupper($request->method());
        $actionTarget = $routeName ?: $path;

        $payload = $this->scrubPayload($request->except([]));

        // Defer write so we never break the user response on audit failure.
        app()->terminating(function () use ($user, $method, $path, $routeName, $actionTarget, $request, $payload, $status) {
            try {
                AuditLog::create([
                    'person_id' => $user->PersonID ?? $user->getAuthIdentifier() ?? null,
                    'actor_name' => $this->actorName($user),
                    'method' => $method,
                    'path' => mb_substr($path, 0, 512),
                    'route_name' => $routeName,
                    'action' => mb_substr("{$method} {$actionTarget}", 0, 512),
                    'ip' => $request->ip(),
                    'user_agent' => mb_substr((string) $request->userAgent(), 0, 2000),
                    'request_payload' => $payload,
                    'response_status' => $status,
                    'created_at' => now(),
                ]);
            } catch (\Throwable $e) {
                report($e);
            }
        });

        return $response;
    }

    private function shouldLog(Request $request): bool
    {
        if (!Auth::check()) {
            return false;
        }

        if (!in_array(strtoupper($request->method()), self::MUTATING, true)) {
            return false;
        }

        $path = ltrim($request->path(), '/');
        foreach (self::SKIP_PREFIXES as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return false;
            }
        }

        return true;
    }

    private function actorName($user): ?string
    {
        if (!$user) {
            return null;
        }

        $name = trim(implode(' ', array_filter([
            $user->FirstName ?? null,
            $user->SecondName ?? null,
            $user->ThirdName ?? null,
        ])));

        return $name !== '' ? $name : null;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function scrubPayload(array $input): array
    {
        $scrubbed = [];
        foreach ($input as $key => $value) {
            if (is_string($key) && preg_match('/password|token|secret|authorization|cookie|_token/i', $key)) {
                $scrubbed[$key] = '[redacted]';
                continue;
            }

            if (is_array($value)) {
                $scrubbed[$key] = $this->scrubPayload($value);
                continue;
            }

            if (is_scalar($value) || $value === null) {
                $scrubbed[$key] = $value;
            } else {
                $scrubbed[$key] = '[object]';
            }
        }

        $json = json_encode($scrubbed, JSON_UNESCAPED_UNICODE);
        if ($json !== false && strlen($json) > 8192) {
            return ['_truncated' => true, '_preview' => mb_substr($json, 0, 8000)];
        }

        return $scrubbed;
    }
}
