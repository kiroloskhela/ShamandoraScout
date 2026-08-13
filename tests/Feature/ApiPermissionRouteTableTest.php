<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApiPermissionRouteTableTest extends TestCase
{
    public function test_every_sanctum_api_route_declares_can_permission(): void
    {
        $missing = [];

        foreach (Route::getRoutes()->getRoutes() as $route) {
            $uri = $route->uri();
            if (! str_starts_with($uri, 'api/')) {
                continue;
            }

            $middleware = $route->gatherMiddleware();
            $hasSanctum = collect($middleware)->contains(
                fn ($m) => is_string($m) && str_contains($m, 'auth:sanctum')
            );
            if (! $hasSanctum) {
                continue;
            }

            // Logout only needs a valid token so revoked staff can still end the session.
            if ($uri === 'api/logout') {
                continue;
            }

            $hasPermission = collect($middleware)->contains(
                fn ($m) => is_string($m) && str_starts_with($m, 'can.permission:')
            );

            if (! $hasPermission) {
                $missing[] = implode('|', $route->methods()).' /'.$uri;
            }
        }

        $this->assertSame([], $missing, 'Sanctum API routes missing can.permission: '.implode(', ', $missing));
    }
}
