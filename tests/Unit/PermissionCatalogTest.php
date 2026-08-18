<?php

namespace Tests\Unit;

use Tests\TestCase;

class PermissionCatalogTest extends TestCase
{
    public function test_every_catalog_key_matches_platform_domain_action_grammar(): void
    {
        $keys = array_keys(config('permissions.keys', []));
        $this->assertNotEmpty($keys);

        foreach ($keys as $key) {
            $this->assertMatchesRegularExpression(
                '/^(web|mobile|api)\.[a-z0-9_]+\.[a-z0-9_]+$/',
                $key,
                "Permission key [{$key}] must be {platform}.{domain}.{action}"
            );
        }
    }

    public function test_non_grantable_keys_are_not_in_the_catalog(): void
    {
        $catalog = config('permissions.keys', []);
        foreach (config('permissions.non_grantable', []) as $key) {
            $this->assertArrayNotHasKey($key, $catalog);
        }
    }

    public function test_seed_map_only_uses_grantable_catalog_keys(): void
    {
        $catalog = config('permissions.keys', []);
        $nonGrantable = config('permissions.non_grantable', []);

        foreach (config('permissions.seed', []) as $role => $keys) {
            $this->assertNotSame('SuperAdmin', $role);
            foreach ($keys as $key) {
                $this->assertArrayHasKey($key, $catalog, "{$role} seeds unknown key {$key}");
                $this->assertFalse(in_array($key, $nonGrantable, true), "{$role} seeds non-grantable {$key}");
            }
        }
    }

    public function test_mkhdom_seed_is_own_only(): void
    {
        $keys = config('permissions.seed.Mkhdom');
        $this->assertNotEmpty($keys);
        $this->assertContains('api.me.view', $keys);
        $this->assertContains('api.attendance.own', $keys);
        $this->assertNotContains('api.mobile.staff', $keys);
        $this->assertNotContains('mobile.attendance.take', $keys);
        $this->assertNotContains('mobile.members.list', $keys);
        $this->assertNotContains('web.people.manage', $keys);
    }
}
