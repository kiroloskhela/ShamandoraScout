<?php

namespace Tests\Unit\Models;

use App\Models\Group;
use App\Models\MedicineDispenseRecord;
use App\Models\MedicineInventory;
use App\Models\MedicineLocation;
use App\Models\MedicineStock;
use App\Models\NewUserEnrolment;
use App\Models\Person;
use App\Models\PersonGroup;
use App\Models\PersonPhoneNumber;
use App\Models\PersonQetaa;
use App\Models\PersonRole;
use App\Models\PersonSanaMarhala;
use App\Models\Qetaa;
use App\Models\Roles;
use App\Models\SanaMarhala;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\TestCase;

/**
 * Pure table/key configuration checks for the Wave 2 Eloquent models.
 * These do not touch a database connection: they only assert that each
 * model's $table / $primaryKey / $incrementing / $timestamps match the
 * hardened schema (see database/migrations/2026_07_15_* "package_*" files
 * and context/database-schema-reality-2026-07-14.md).
 */
class EloquentCoreModelsTest extends TestCase
{
    public function test_user_maps_to_person_information(): void
    {
        $model = new User();

        $this->assertSame('PersonInformation', $model->getTable());
        $this->assertSame('PersonID', $model->getKeyName());
        $this->assertFalse($model->usesTimestamps());
        $this->assertTrue($model->getIncrementing());
    }

    public function test_roles_has_role_id_primary_key_and_is_not_authenticatable(): void
    {
        $model = new Roles();

        $this->assertSame('Roles', $model->getTable());
        $this->assertSame('RoleID', $model->getKeyName());
        $this->assertFalse($model->usesTimestamps());
        $this->assertInstanceOf(Model::class, $model);
        $this->assertNotInstanceOf(\Illuminate\Foundation\Auth\User::class, $model);
    }

    public function test_person_role_pivot_model(): void
    {
        $model = new PersonRole();

        $this->assertSame('PersonRole', $model->getTable());
        $this->assertSame('PersonRoleID', $model->getKeyName());
        $this->assertFalse($model->usesTimestamps());
    }

    public function test_group_maps_to_group_table(): void
    {
        $model = new Group();

        $this->assertSame('GroupTable', $model->getTable());
        $this->assertSame('GroupID', $model->getKeyName());
        $this->assertFalse($model->usesTimestamps());
    }

    public function test_person_group_uses_person_group_role_id_primary_key(): void
    {
        $model = new PersonGroup();

        $this->assertSame('PersonGroup', $model->getTable());
        $this->assertSame('PersonGroupRoleID', $model->getKeyName());
        $this->assertFalse($model->usesTimestamps());
    }

    public function test_qetaa_lookup_table(): void
    {
        $model = new Qetaa();

        $this->assertSame('Qetaa', $model->getTable());
        $this->assertSame('QetaaID', $model->getKeyName());
        $this->assertFalse($model->getIncrementing());
        $this->assertFalse($model->usesTimestamps());
    }

    public function test_person_qetaa_junction_table_has_no_surrogate_key(): void
    {
        $model = new PersonQetaa();

        $this->assertSame('PersonQetaa', $model->getTable());
        $this->assertSame('PersonID', $model->getKeyName());
        $this->assertFalse($model->getIncrementing());
        $this->assertFalse($model->usesTimestamps());
    }

    public function test_sana_marhala_lookup_table(): void
    {
        $model = new SanaMarhala();

        $this->assertSame('SanaMarhala', $model->getTable());
        $this->assertSame('SanaMarhalaID', $model->getKeyName());
        $this->assertFalse($model->getIncrementing());
        $this->assertFalse($model->usesTimestamps());
    }

    public function test_person_sana_marhala_junction_table_has_no_surrogate_key(): void
    {
        $model = new PersonSanaMarhala();

        $this->assertSame('PersonSanaMarhala', $model->getTable());
        $this->assertSame('PersonID', $model->getKeyName());
        $this->assertFalse($model->getIncrementing());
        $this->assertFalse($model->usesTimestamps());
    }

    public function test_new_user_enrolment_uses_surrogate_id_not_person_id(): void
    {
        $model = new NewUserEnrolment();

        $this->assertSame('NewUsersInformation', $model->getTable());
        $this->assertSame('id', $model->getKeyName());
        $this->assertNotSame('PersonID', $model->getKeyName());
        $this->assertFalse($model->usesTimestamps());
        $this->assertContains('PersonID', $model->getFillable());
    }

    public function test_person_model_is_a_deprecated_alias_of_person_information(): void
    {
        $model = new Person();

        $this->assertSame('PersonInformation', $model->getTable());
        $this->assertSame('PersonID', $model->getKeyName());
        $this->assertFalse($model->usesTimestamps());
        $this->assertTrue(method_exists($model, 'roles'));

        $doc = (new \ReflectionClass(Person::class))->getDocComment() ?: '';
        $this->assertStringContainsString('@deprecated', $doc);
        $this->assertStringContainsString('User', $doc);
    }

    public function test_medicine_models_map_to_laravel_medicine_tables(): void
    {
        $inventory = new MedicineInventory();
        $this->assertSame('MedicineInventory', $inventory->getTable());
        $this->assertSame('MedicineID', $inventory->getKeyName());
        $this->assertNotEmpty($inventory->getFillable());
        $this->assertContains('MedicineName', $inventory->getFillable());

        $location = new MedicineLocation();
        $this->assertSame('MedicineLocations', $location->getTable());
        $this->assertSame('LocationID', $location->getKeyName());

        $stock = new MedicineStock();
        $this->assertSame('MedicineStock', $stock->getTable());
        $this->assertSame('MedicineStockID', $stock->getKeyName());

        $dispense = new MedicineDispenseRecord();
        $this->assertSame('MedicineDispenseRecords', $dispense->getTable());
        $this->assertSame('MedicineDispenseID', $dispense->getKeyName());
    }

    public function test_person_phone_number_satellite_model(): void
    {
        $model = new PersonPhoneNumber();

        $this->assertSame('PersonPhoneNumbers', $model->getTable());
        $this->assertSame('PersonID', $model->getKeyName());
        $this->assertFalse($model->getIncrementing());
        $this->assertFalse($model->usesTimestamps());
    }

    public function test_user_declares_org_and_role_relation_methods(): void
    {
        // Relation *building* (belongsToMany/hasMany) requires a live DB
        // connection resolver, which these tests intentionally avoid. We
        // only assert the relation methods exist on the model.
        foreach (['role', 'personGroups', 'groups', 'qetaas', 'sanaMarhalas', 'image', 'password'] as $method) {
            $this->assertTrue(method_exists(User::class, $method), "User::{$method}() should exist");
        }
    }
}
