<?php

namespace Tests\Feature;

use App\Jobs\SendWhatsAppCampaignMessage;
use App\Models\User;
use App\Models\WhatsAppCampaign;
use App\Models\WhatsAppCampaignRecipient;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WhatsAppCampaignTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'sqlite') {
            $this->markTestSkipped('WhatsAppCampaignTest requires sqlite in-memory.');
        }

        config([
            'services.whatsapp.bridge_url' => 'http://127.0.0.1:3010/send',
            'services.whatsapp.bridge_token' => 'test-token',
        ]);

        foreach ([
            'whatsapp_campaign_recipients',
            'whatsapp_campaigns',
            'PersonQetaa',
            'Qetaa',
            'PersonBlackList',
            'PersonPhoneNumbers',
            'PersonRole',
            'Roles',
            'PersonImages',
            'PersonInformation',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('PersonInformation', function (Blueprint $table) {
            $table->increments('PersonID');
            $table->string('ShamandoraCode')->nullable();
            $table->string('FirstName')->nullable();
            $table->string('SecondName')->nullable();
            $table->string('ThirdName')->nullable();
            $table->string('FourthName')->nullable();
            $table->string('Gender')->nullable();
        });

        Schema::create('PersonImages', function (Blueprint $table) {
            $table->increments('PersonImageID');
            $table->unsignedInteger('PersonID')->nullable();
            $table->string('PersonSystemImagePath')->nullable();
            $table->string('PersonSystemImageThumbnailPath')->nullable();
        });

        Schema::create('Roles', function (Blueprint $table) {
            $table->increments('RoleID');
            $table->string('RoleName');
            $table->text('RoleDescription')->nullable();
        });

        Schema::create('PersonRole', function (Blueprint $table) {
            $table->increments('PersonRoleID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('RoleID');
            $table->unsignedInteger('RequestPersonID')->nullable();
        });

        Schema::create('PersonPhoneNumbers', function (Blueprint $table) {
            $table->unsignedInteger('PersonID')->primary();
            $table->string('PersonPersonalMobileNumber');
            $table->string('IsOPersonalPhoneNumberHavingWhatsapp')->nullable();
            $table->unsignedTinyInteger('WhatsAppConsent')->default(1);
            $table->unsignedTinyInteger('DoNotContact')->default(0);
        });

        Schema::create('PersonBlackList', function (Blueprint $table) {
            $table->increments('BlackListID');
            $table->unsignedInteger('PersonID');
        });

        Schema::create('Qetaa', function (Blueprint $table) {
            $table->increments('QetaaID');
            $table->string('QetaaName')->nullable();
        });

        Schema::create('PersonQetaa', function (Blueprint $table) {
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QetaaID');
        });

        Schema::create('whatsapp_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('message_template');
            $table->string('status', 32)->default('draft');
            $table->string('missing_variable_behavior', 16)->default('fallback');
            $table->string('fallback_name')->nullable();
            $table->unsignedInteger('min_delay_seconds')->default(8);
            $table->unsignedInteger('max_delay_seconds')->default(15);
            $table->unsignedInteger('max_messages_per_hour')->default(60);
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('whatsapp_campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedInteger('person_id');
            $table->string('phone', 32);
            $table->text('personalized_message')->nullable();
            $table->string('status', 32)->default('pending');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->string('whatsapp_message_id')->nullable();
            $table->text('error_message')->nullable();
            $table->string('error_kind', 16)->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->unique(['campaign_id', 'person_id']);
        });
    }

    private function createSuperAdmin(): User
    {
        $user = User::create([
            'FirstName' => 'Super',
            'SecondName' => 'Admin',
            'ThirdName' => 'Test',
            'ShamandoraCode' => 'WA' . uniqid(),
        ]);

        $roleId = DB::table('Roles')->insertGetId([
            'RoleName' => 'SuperAdmin',
            'RoleDescription' => 'test',
        ]);

        DB::table('PersonRole')->insert([
            'PersonID' => $user->PersonID,
            'RoleID' => $roleId,
        ]);

        return $user->fresh();
    }

    private function seedPerson(string $phone = '01011112222', int $dnc = 0): int
    {
        $id = DB::table('PersonInformation')->insertGetId([
            'FirstName' => 'Ali',
            'SecondName' => 'Test',
            'ThirdName' => 'User',
            'ShamandoraCode' => 'SH' . random_int(100, 999),
            'Gender' => 'Male',
        ]);

        DB::table('PersonPhoneNumbers')->insert([
            'PersonID' => $id,
            'PersonPersonalMobileNumber' => $phone,
            'IsOPersonalPhoneNumberHavingWhatsapp' => '1',
            'WhatsAppConsent' => 1,
            'DoNotContact' => $dnc,
        ]);

        return $id;
    }

    public function test_guest_cannot_access_campaigns(): void
    {
        $this->get('/whatsapp/campaigns')->assertRedirect();
    }

    public function test_superadmin_can_create_draft_and_confirm_dispatches_job(): void
    {
        Queue::fake();
        $admin = $this->createSuperAdmin();
        $personId = $this->seedPerson();

        $response = $this->actingAs($admin)->post('/whatsapp/campaigns', [
            'name' => 'Test Campaign',
            'message_template' => 'Hello {name}',
            'missing_variable_behavior' => 'fallback',
            'fallback_name' => 'صديقنا',
            'min_delay_seconds' => 5,
            'max_delay_seconds' => 10,
            'max_messages_per_hour' => 30,
            'person_ids' => [$personId],
        ]);

        $campaign = WhatsAppCampaign::first();
        $this->assertNotNull($campaign);
        $response->assertRedirect(route('whatsapp.campaigns.show', $campaign));
        $this->assertSame('draft', $campaign->status);
        $this->assertSame(1, $campaign->recipients()->count());
        $this->assertStringContainsString('Ali Test User', (string) $campaign->recipients()->first()->personalized_message);

        $this->actingAs($admin)->post(route('whatsapp.campaigns.confirm', $campaign))
            ->assertRedirect();

        $campaign->refresh();
        $this->assertContains($campaign->status, ['queued', 'running']);

        Queue::assertPushed(SendWhatsAppCampaignMessage::class);
    }

    public function test_dnc_person_is_excluded_from_selection(): void
    {
        $admin = $this->createSuperAdmin();
        $ok = $this->seedPerson('01022223333', 0);
        $blocked = $this->seedPerson('01033334444', 1);

        $this->actingAs($admin)->post('/whatsapp/campaigns', [
            'name' => 'DNC Test',
            'message_template' => 'Hi {name}',
            'person_ids' => [$ok, $blocked],
        ]);

        $campaign = WhatsAppCampaign::first();
        $ids = $campaign->recipients()->pluck('person_id')->all();
        $this->assertContains($ok, $ids);
        $this->assertNotContains($blocked, $ids);
    }

    public function test_pause_stops_can_resume(): void
    {
        Queue::fake();
        $admin = $this->createSuperAdmin();
        $personId = $this->seedPerson();

        $this->actingAs($admin)->post('/whatsapp/campaigns', [
            'name' => 'Pause Test',
            'message_template' => 'Hi {name}',
            'person_ids' => [$personId],
        ]);

        $campaign = WhatsAppCampaign::first();
        $this->actingAs($admin)->post(route('whatsapp.campaigns.confirm', $campaign));
        $this->actingAs($admin)->post(route('whatsapp.campaigns.pause', $campaign));
        $campaign->refresh();
        $this->assertSame(WhatsAppCampaign::STATUS_PAUSED, $campaign->status);

        $this->actingAs($admin)->post(route('whatsapp.campaigns.resume', $campaign));
        $campaign->refresh();
        $this->assertSame(WhatsAppCampaign::STATUS_RUNNING, $campaign->status);
    }

    public function test_job_sends_via_bridge_and_marks_sent(): void
    {
        Http::fake([
            'http://127.0.0.1:3010/send' => Http::response(['ok' => true, 'to' => '201011112222', 'messageId' => 'abc'], 200),
        ]);

        $admin = $this->createSuperAdmin();
        $personId = $this->seedPerson();

        $campaign = WhatsAppCampaign::create([
            'name' => 'Job Test',
            'message_template' => 'Hi {name}',
            'status' => WhatsAppCampaign::STATUS_RUNNING,
            'missing_variable_behavior' => 'fallback',
            'fallback_name' => 'x',
            'min_delay_seconds' => 1,
            'max_delay_seconds' => 1,
            'max_messages_per_hour' => 60,
            'created_by' => $admin->PersonID,
            'started_at' => now(),
        ]);

        $recipient = WhatsAppCampaignRecipient::create([
            'campaign_id' => $campaign->id,
            'person_id' => $personId,
            'phone' => '+201011112222',
            'personalized_message' => 'Hi Ali',
            'status' => WhatsAppCampaignRecipient::STATUS_QUEUED,
        ]);

        (new SendWhatsAppCampaignMessage($campaign->id, $recipient->id))->handle(
            app(\App\Services\WhatsAppBridgeClient::class),
            app(\App\Domain\WhatsApp\WhatsAppCampaignService::class)
        );

        $recipient->refresh();
        $this->assertSame(WhatsAppCampaignRecipient::STATUS_SENT, $recipient->status);
        $this->assertSame('abc', $recipient->whatsapp_message_id);
    }
}
