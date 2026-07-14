<?php

namespace Tests\Feature;

use App\Http\Controllers\API\PersonApiController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regression tests for the IDOR (Insecure Direct Object Reference) fix in
 * PersonApiController: /api/show-persons, /api/person/{id} and
 * /api/calendar/{id} must always be scoped to the *authenticated* user's
 * PersonID, never to a client-supplied id (query input or route segment).
 *
 * These are deliberately unit-style tests: they call the controller methods
 * directly and swap the DB facade for a lightweight recorder instead of
 * hitting a real database, since the target environment's full schema
 * (schema.sql) is not something we want these security-regression tests to
 * depend on.
 */
class PersonApiIdorTest extends TestCase
{
    public const AUTHENTICATED_PERSON_ID = 1;
    public const ATTACKER_SUPPLIED_ID = 999;

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    /**
     * A stand-in for Illuminate's fluent query builder. It accepts any
     * chained call (leftJoin, join, where, select, distinct, orderBy, ...),
     * records it, and returns itself so the controller's full query chain
     * executes without touching a real database. Terminal calls (get/first)
     * return safe, inert defaults.
     */
    private function fakeQueryBuilder(): object
    {
        return new class {
            /** @var array<int, array{0: string, 1: array}> */
            public array $calls = [];

            public function __call($name, $arguments)
            {
                $this->calls[] = [$name, $arguments];

                if ($name === 'get') {
                    return collect();
                }

                if ($name === 'first') {
                    return (object) ['PersonID' => PersonApiIdorTest::AUTHENTICATED_PERSON_ID];
                }

                return $this;
            }

            public function whereArgsFor(string $column): array
            {
                foreach ($this->calls as [$method, $args]) {
                    if ($method === 'where' && ($args[0] ?? null) === $column) {
                        return $args;
                    }
                }

                return [];
            }
        };
    }

    private function mockDbTable(): object
    {
        $builder = $this->fakeQueryBuilder();
        DB::shouldReceive('table')->andReturn($builder);
        DB::shouldReceive('raw')->andReturnUsing(fn ($value) => $value);

        return $builder;
    }

    private function requestAuthenticatedAs(int $personId, array $query = []): Request
    {
        $request = Request::create('/api/whatever', 'GET', $query);

        $user = new User();
        $user->PersonID = $personId;

        $request->setUserResolver(fn () => $user);

        return $request;
    }

    public function test_show_persons_ignores_client_supplied_id_and_uses_authenticated_person_id(): void
    {
        $builder = $this->mockDbTable();

        // Attacker is authenticated as PersonID 1 but tries to pass another
        // user's id in the request in order to read their group's persons.
        $request = $this->requestAuthenticatedAs(
            self::AUTHENTICATED_PERSON_ID,
            ['id' => self::ATTACKER_SUPPLIED_ID]
        );

        $response = (new PersonApiController())->showPersons($request);

        $this->assertSame(200, $response->getStatusCode());

        $whereArgs = $builder->whereArgsFor('pg2.PersonID');
        $this->assertNotEmpty($whereArgs, 'Expected the query to be scoped via where(pg2.PersonID, ...).');
        $this->assertSame(
            self::AUTHENTICATED_PERSON_ID,
            $whereArgs[1],
            'The list of persons must be scoped to the authenticated PersonID.'
        );
        $this->assertNotSame(
            self::ATTACKER_SUPPLIED_ID,
            $whereArgs[1],
            'A client-supplied id must never be used to scope which persons are returned.'
        );
    }

    public function test_show_profile_ignores_route_id_and_uses_authenticated_person_id(): void
    {
        $builder = $this->mockDbTable();

        $request = $this->requestAuthenticatedAs(self::AUTHENTICATED_PERSON_ID);

        // Attacker is authenticated as PersonID 1 but requests
        // GET /api/person/999 hoping to view someone else's profile.
        $response = (new PersonApiController())->ShowProfile($request, self::ATTACKER_SUPPLIED_ID);

        $this->assertSame(200, $response->getStatusCode());

        $whereArgs = $builder->whereArgsFor('PersonInformation.PersonID');
        $this->assertNotEmpty($whereArgs, 'Expected the query to be scoped via where(PersonInformation.PersonID, ...).');
        $this->assertSame(
            self::AUTHENTICATED_PERSON_ID,
            $whereArgs[1],
            'The profile returned must be scoped to the authenticated PersonID.'
        );
        $this->assertNotSame(
            self::ATTACKER_SUPPLIED_ID,
            $whereArgs[1],
            'The {id} route parameter must never be used to select another user\'s profile.'
        );
    }

    public function test_show_calendar_ignores_route_id_and_uses_authenticated_person_id(): void
    {
        $capturedBindings = null;

        DB::shouldReceive('select')
            ->once()
            ->andReturnUsing(function ($sql, $bindings) use (&$capturedBindings) {
                $capturedBindings = $bindings;

                return [];
            });

        $request = $this->requestAuthenticatedAs(self::AUTHENTICATED_PERSON_ID);

        // Attacker is authenticated as PersonID 1 but requests
        // GET /api/calendar/999 hoping to view someone else's events.
        $response = (new PersonApiController())->ShowCalendar($request, self::ATTACKER_SUPPLIED_ID);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(
            [self::AUTHENTICATED_PERSON_ID],
            $capturedBindings,
            'The calendar query must be bound to the authenticated PersonID, not the {id} route parameter.'
        );
    }
}
