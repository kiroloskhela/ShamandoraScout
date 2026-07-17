<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Place bookings are owner-scoped. Cross-user access returns 404 (not 403).
 */
class PlaceBookingApiIdorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('PlaceBookings');
        Schema::dropIfExists('Place');
        Schema::dropIfExists('Locations');
        Schema::dropIfExists('Qetaa');
        Schema::dropIfExists('PersonInformation');
        Schema::dropIfExists('personal_access_tokens');

        Schema::create('PersonInformation', function (Blueprint $table) {
            $table->increments('PersonID');
            $table->string('ShamandoraCode')->nullable();
            $table->string('FirstName')->nullable();
            $table->string('SecondName')->nullable();
            $table->string('ThirdName')->nullable();
        });

        Schema::create('Locations', function (Blueprint $table) {
            $table->increments('LocationID');
            $table->string('LocationName');
        });

        Schema::create('Place', function (Blueprint $table) {
            $table->increments('PlaceID');
            $table->string('PlaceName');
            $table->unsignedInteger('LocationID');
        });

        Schema::create('Qetaa', function (Blueprint $table) {
            $table->increments('QetaaID');
            $table->string('QetaaName');
        });

        Schema::create('PlaceBookings', function (Blueprint $table) {
            $table->increments('BookingID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('PlaceID');
            $table->unsignedInteger('QetaaID')->nullable();
            $table->date('BookingDate');
            $table->string('TimeFrom');
            $table->string('TimeTo');
            $table->string('Status')->default('pending');
            $table->text('UserNote')->nullable();
            $table->text('AdminNote')->nullable();
            $table->unsignedInteger('ReviewedBy')->nullable();
            $table->timestamp('ReviewedAt')->nullable();
            $table->unsignedInteger('ApprovedPlaceID')->nullable();
            $table->string('ApprovedTimeFrom')->nullable();
            $table->string('ApprovedTimeTo')->nullable();
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

        $locationId = (int) DB::table('Locations')->insertGetId([
            'LocationName' => 'Main Building',
        ]);
        DB::table('Place')->insert([
            'PlaceName' => 'Room A',
            'LocationID' => $locationId,
        ]);
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

    private function seedBooking(int $personId, string $status = 'pending'): int
    {
        $placeId = (int) DB::table('Place')->value('PlaceID');

        return (int) DB::table('PlaceBookings')->insertGetId([
            'PersonID' => $personId,
            'PlaceID' => $placeId,
            'BookingDate' => '2026-07-10',
            'TimeFrom' => '10:00',
            'TimeTo' => '12:00',
            'Status' => $status,
            'UserNote' => 'need room',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_unauthenticated_index_is_rejected(): void
    {
        $this->getJson('/api/place_bookings')->assertStatus(401);
    }

    public function test_index_is_scoped_to_owner(): void
    {
        $owner = $this->createUser('OWN');
        $other = $this->createUser('OTH');
        $mine = $this->seedBooking($owner->PersonID);
        $this->seedBooking($other->PersonID);

        $this->withHeaders($this->authHeaders($owner))
            ->getJson('/api/place_bookings')
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('count', 1)
            ->assertJsonPath('bookings.0.BookingID', $mine)
            ->assertJsonPath('bookings.0.PlaceName', 'Room A');
    }

    public function test_cross_user_show_is_not_found(): void
    {
        $owner = $this->createUser('OWN');
        $other = $this->createUser('OTH');
        $bookingId = $this->seedBooking($owner->PersonID);

        $this->withHeaders($this->authHeaders($other))
            ->getJson("/api/place_bookings/{$bookingId}")
            ->assertStatus(404)
            ->assertJsonPath('message', 'Booking not found');
    }

    public function test_owner_cannot_update_after_review(): void
    {
        $owner = $this->createUser('OWN');
        $bookingId = $this->seedBooking($owner->PersonID, 'approved');
        $placeId = (int) DB::table('Place')->value('PlaceID');

        $this->withHeaders($this->authHeaders($owner))
            ->putJson("/api/place_bookings/{$bookingId}", [
                'place_id' => $placeId,
                'booking_date' => '2026-07-11',
                'time_from' => '09:00',
                'time_to' => '11:00',
            ])
            ->assertForbidden()
            ->assertJsonPath('message', 'Cannot update after review');
    }

    public function test_owner_cannot_destroy_after_review(): void
    {
        $owner = $this->createUser('OWN');
        $bookingId = $this->seedBooking($owner->PersonID, 'rejected');

        $this->withHeaders($this->authHeaders($owner))
            ->deleteJson("/api/place_bookings/{$bookingId}")
            ->assertForbidden()
            ->assertJsonPath('message', 'Cannot delete after review');
    }

    public function test_invalid_time_order_is_rejected(): void
    {
        $owner = $this->createUser('OWN');
        $placeId = (int) DB::table('Place')->value('PlaceID');

        $this->withHeaders($this->authHeaders($owner))
            ->postJson('/api/place_bookings', [
                'place_id' => $placeId,
                'booking_date' => '2026-07-12',
                'time_from' => '14:00',
                'time_to' => '13:00',
            ])
            ->assertStatus(422);
    }
}
