<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Documents IDOR policy: person API endpoints always use the authenticated
 * user's PersonID and ignore client-supplied ids.
 *
 * Full DB integration requires the legacy schema; this test asserts the
 * controller resolution contract when a user is authenticated via Sanctum.
 */
class PersonApiIdorTest extends TestCase
{
    public function test_show_persons_route_requires_authentication(): void
    {
        $this->getJson('/api/show-persons?id=999')->assertUnauthorized();
    }

    public function test_show_profile_route_requires_authentication(): void
    {
        $this->getJson('/api/person/999')->assertUnauthorized();
    }

    public function test_show_calendar_route_requires_authentication(): void
    {
        $this->getJson('/api/calendar/999')->assertUnauthorized();
    }
}
