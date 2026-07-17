<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Custody requests are owner-scoped. Cross-user access returns 404 (not 403).
 */
class CustodyApiIdorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('CustodyRequestItems');
        Schema::dropIfExists('CustodyRequests');
        Schema::dropIfExists('Inventory');
        Schema::dropIfExists('Qetaa');
        Schema::dropIfExists('EventType');
        Schema::dropIfExists('PersonInformation');
        Schema::dropIfExists('personal_access_tokens');

        Schema::create('PersonInformation', function (Blueprint $table) {
            $table->increments('PersonID');
            $table->string('ShamandoraCode')->nullable();
            $table->string('FirstName')->nullable();
            $table->string('SecondName')->nullable();
            $table->string('ThirdName')->nullable();
        });

        Schema::create('Inventory', function (Blueprint $table) {
            $table->increments('InventoryID');
            $table->string('ItemName');
            $table->unsignedInteger('ItemQuantity')->default(0);
            $table->string('ItemMeasuringUnit')->nullable();
        });

        Schema::create('Qetaa', function (Blueprint $table) {
            $table->increments('QetaaID');
            $table->string('QetaaName');
        });

        Schema::create('EventType', function (Blueprint $table) {
            $table->increments('EventTypeID');
            $table->string('EventTypeName');
        });

        Schema::create('CustodyRequests', function (Blueprint $table) {
            $table->increments('RequestID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QetaaID')->nullable();
            $table->unsignedInteger('EventTypeID')->nullable();
            $table->date('DateFrom');
            $table->date('DateTo');
            $table->string('Status')->default('pending');
            $table->text('UserNote')->nullable();
            $table->text('AdminNote')->nullable();
            $table->unsignedInteger('ReviewedBy')->nullable();
            $table->timestamp('ReviewedAt')->nullable();
            $table->timestamps();
        });

        Schema::create('CustodyRequestItems', function (Blueprint $table) {
            $table->increments('RequestItemID');
            $table->unsignedInteger('RequestID');
            $table->unsignedInteger('InventoryID');
            $table->string('ItemNameSnapshot');
            $table->string('ItemUnitSnapshot')->nullable();
            $table->unsignedInteger('QtyRequested');
            $table->unsignedInteger('QtyApproved')->nullable();
            $table->text('AdminItemNote')->nullable();
            $table->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuidMorphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    private function createUser(string $code = 'TST'): User
    {
        return User::create([
            'FirstName' => 'Test',
            'SecondName' => 'User',
            'ThirdName' => 'A',
            'ShamandoraCode' => $code.uniqid(),
        ]);
    }

    private function authHeaders(User $user): array
    {
        return ['Authorization' => 'Bearer '.$user->createToken('test-token')->plainTextToken];
    }

    private function seedRequest(int $personId, string $status = 'pending'): int
    {
        return (int) DB::table('CustodyRequests')->insertGetId([
            'PersonID' => $personId,
            'DateFrom' => '2026-07-01',
            'DateTo' => '2026-07-02',
            'Status' => $status,
            'UserNote' => 'note',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_unauthenticated_index_is_rejected(): void
    {
        $this->getJson('/api/custody/requests')->assertStatus(401);
    }

    public function test_index_is_scoped_to_owner(): void
    {
        $owner = $this->createUser('OWN');
        $other = $this->createUser('OTH');
        $mine = $this->seedRequest($owner->PersonID);
        $this->seedRequest($other->PersonID);

        $response = $this->withHeaders($this->authHeaders($owner))
            ->getJson('/api/custody/requests');

        $response->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('count', 1)
            ->assertJsonPath('requests.0.RequestID', $mine);
    }

    public function test_cross_user_show_is_not_found(): void
    {
        $owner = $this->createUser('OWN');
        $other = $this->createUser('OTH');
        $requestId = $this->seedRequest($owner->PersonID);

        $this->withHeaders($this->authHeaders($other))
            ->getJson("/api/custody/requests/{$requestId}")
            ->assertStatus(404)
            ->assertJsonPath('message', 'Request not found');
    }

    public function test_owner_cannot_update_after_review(): void
    {
        $owner = $this->createUser('OWN');
        $requestId = $this->seedRequest($owner->PersonID, 'approved');
        $inventoryId = (int) DB::table('Inventory')->insertGetId([
            'ItemName' => 'Tent',
            'ItemQuantity' => 5,
            'ItemMeasuringUnit' => 'piece',
        ]);

        $this->withHeaders($this->authHeaders($owner))
            ->putJson("/api/custody/requests/{$requestId}", [
                'date_from' => '2026-07-01',
                'date_to' => '2026-07-02',
                'items' => [
                    ['inventory_id' => $inventoryId, 'qty' => 1],
                ],
            ])
            ->assertForbidden()
            ->assertJsonPath('message', 'Cannot update after review');
    }

    public function test_owner_cannot_destroy_after_review(): void
    {
        $owner = $this->createUser('OWN');
        $requestId = $this->seedRequest($owner->PersonID, 'rejected');

        $this->withHeaders($this->authHeaders($owner))
            ->deleteJson("/api/custody/requests/{$requestId}")
            ->assertForbidden()
            ->assertJsonPath('message', 'Cannot delete after review');
    }

    public function test_owner_can_show_own_pending_request(): void
    {
        $owner = $this->createUser('OWN');
        $requestId = $this->seedRequest($owner->PersonID);
        DB::table('CustodyRequestItems')->insert([
            'RequestID' => $requestId,
            'InventoryID' => 1,
            'ItemNameSnapshot' => 'Tent',
            'ItemUnitSnapshot' => 'piece',
            'QtyRequested' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withHeaders($this->authHeaders($owner))
            ->getJson("/api/custody/requests/{$requestId}")
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('request.RequestID', $requestId)
            ->assertJsonPath('items.0.ItemNameSnapshot', 'Tent');
    }
}
