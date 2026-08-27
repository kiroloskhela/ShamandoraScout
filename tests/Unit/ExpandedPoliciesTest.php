<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\WhatsAppCampaign;
use App\Policies\CustodyPolicy;
use App\Policies\EnrolmentPolicy;
use App\Policies\MedicinePolicy;
use App\Policies\WhatsAppCampaignPolicy;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ExpandedPoliciesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'NewUsersInformation',
            'CustodyRequests',
            'PersonQetaa',
            'PersonRole',
            'Roles',
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

        Schema::create('PersonQetaa', function (Blueprint $table) {
            $table->increments('PersonQetaaID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QetaaID');
        });

        Schema::create('CustodyRequests', function (Blueprint $table) {
            $table->increments('RequestID');
            $table->unsignedInteger('PersonID');
            $table->string('Status')->default('pending');
        });

        Schema::create('NewUsersInformation', function (Blueprint $table) {
            $table->increments('PersonID');
            $table->unsignedInteger('QetaaID')->nullable();
            $table->string('FirstName')->nullable();
        });
    }

    private function createUser(?string $code = null): User
    {
        return User::create([
            'FirstName' => 'Test',
            'SecondName' => 'User',
            'ThirdName' => 'X',
            'ShamandoraCode' => $code ?? ('T'.uniqid()),
        ]);
    }

    private function attachRole(User $user, string $roleName): void
    {
        $roleId = DB::table('Roles')->where('RoleName', $roleName)->value('RoleID');
        if (! $roleId) {
            $roleId = DB::table('Roles')->insertGetId([
                'RoleName' => $roleName,
                'RoleDescription' => $roleName,
            ]);
        }

        DB::table('PersonRole')->insert([
            'PersonID' => $user->PersonID,
            'RoleID' => $roleId,
        ]);
    }

    public function test_medicine_policy_allows_first_aid_and_denies_regular_user(): void
    {
        $aid = $this->createUser('M1');
        $this->attachRole($aid, 'AdminFirstAid');
        $regular = $this->createUser('M2');
        $policy = new MedicinePolicy;

        $this->assertTrue($policy->manage($aid));
        $this->assertTrue($policy->dispense($aid));
        $this->assertFalse($policy->manage($regular));
        $this->assertTrue(Gate::forUser($aid)->allows('medicine.manage'));
        $this->assertTrue(Gate::forUser($regular)->denies('medicine.manage'));
    }

    public function test_custody_policy_owner_pending_and_reviewer_roles(): void
    {
        $owner = $this->createUser('C1');
        $other = $this->createUser('C2');
        $reviewer = $this->createUser('C3');
        $this->attachRole($reviewer, 'AdminInventory');
        $policy = new CustodyPolicy;

        $requestId = (int) DB::table('CustodyRequests')->insertGetId([
            'PersonID' => $owner->PersonID,
            'Status' => 'pending',
        ]);

        $this->assertTrue($policy->view($owner, $requestId));
        $this->assertTrue($policy->update($owner, $requestId));
        $this->assertFalse($policy->view($other, $requestId));
        $this->assertFalse($policy->update($other, $requestId));
        $this->assertTrue($policy->review($reviewer));
        $this->assertTrue($policy->view($reviewer, $requestId));
        $this->assertTrue(Gate::forUser($owner)->allows('custody.update', $requestId));
        $this->assertTrue(Gate::forUser($other)->denies('custody.update', $requestId));
    }

    public function test_whatsapp_campaign_policy_superadmin_only(): void
    {
        $admin = $this->createUser('W1');
        $this->attachRole($admin, 'SuperAdmin');
        $regular = $this->createUser('W2');
        $policy = new WhatsAppCampaignPolicy;
        $campaign = new WhatsAppCampaign;

        $this->assertTrue($policy->viewAny($admin));
        $this->assertTrue($policy->manage($admin, $campaign));
        $this->assertFalse($policy->viewAny($regular));
        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', WhatsAppCampaign::class));
        $this->assertTrue(Gate::forUser($regular)->denies('viewAny', WhatsAppCampaign::class));
    }

    public function test_enrolment_policy_admin_qetaa_unscoped_and_migrate_superadmin(): void
    {
        $adminWithSector = $this->createUser('E1');
        $this->attachRole($adminWithSector, 'AdminQetaa');
        $adminWithoutSector = $this->createUser('E3');
        $this->attachRole($adminWithoutSector, 'AdminQetaa');
        $super = $this->createUser('E2');
        $this->attachRole($super, 'SuperAdmin');
        $policy = new EnrolmentPolicy;

        DB::table('PersonQetaa')->insert([
            'PersonID' => $adminWithSector->PersonID,
            'QetaaID' => 1,
        ]);

        $sharedId = (int) DB::table('NewUsersInformation')->insertGetId([
            'QetaaID' => 1,
            'FirstName' => 'Shared',
        ]);
        $otherId = (int) DB::table('NewUsersInformation')->insertGetId([
            'QetaaID' => 2,
            'FirstName' => 'Other',
        ]);

        $this->assertTrue($policy->view($adminWithSector, $sharedId));
        $this->assertTrue($policy->approve($adminWithSector, $sharedId));
        $this->assertTrue($policy->update($adminWithSector, $otherId));
        $this->assertTrue($policy->delete($adminWithSector, $otherId));
        $this->assertTrue($policy->approve($adminWithoutSector, $otherId));
        $this->assertFalse($policy->view($adminWithSector, 99999));
        $this->assertTrue($policy->view($super, $otherId));
        $this->assertTrue($policy->migrate($super));
        $this->assertFalse($policy->migrate($adminWithSector));
        $this->assertTrue(Gate::forUser($adminWithSector)->allows('enrolment.view', $otherId));
        $this->assertTrue(Gate::forUser($adminWithSector)->allows('enrolment.approve', $otherId));
        $this->assertTrue(Gate::forUser($adminWithSector)->allows('enrolment.update', $otherId));
        $this->assertTrue(Gate::forUser($adminWithSector)->allows('enrolment.delete', $otherId));
        $this->assertTrue(Gate::forUser($super)->allows('enrolment.migrate'));
    }
}
