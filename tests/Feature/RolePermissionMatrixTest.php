<?php

namespace Tests\Feature;

use App\Domain\Authz\PermissionService;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RolePermissionMatrixTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('PersonSystemPassword');
        Schema::dropIfExists('PersonRole');
        Schema::dropIfExists('Roles');
        Schema::dropIfExists('PersonImages');
        Schema::dropIfExists('PersonInformation');

        Schema::create('PersonInformation', function (Blueprint $table) {
            $table->increments('PersonID');
            $table->string('ShamandoraCode')->nullable();
            $table->string('FirstName')->nullable();
            $table->string('SecondName')->nullable();
            $table->string('ThirdName')->nullable();
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
        });

        Schema::create('PersonSystemPassword', function (Blueprint $table) {
            $table->unsignedInteger('PersonID')->primary();
            $table->string('Password');
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('RoleID');
            $table->string('permission_key', 120);
            $table->timestamps();
            $table->unique(['RoleID', 'permission_key']);
        });
    }

    public function test_matrix_update_is_behind_csrf_middleware(): void
    {
        $route = Route::getRoutes()->getByName('role-permissions.update');
        $this->assertNotNull($route);

        $middleware = collect($route->gatherMiddleware())->map(fn ($m) => (string) $m);
        $this->assertTrue(
            $middleware->contains('web') || $middleware->contains(VerifyCsrfToken::class),
            'Matrix update must run through web CSRF middleware.'
        );

        $this->actingAs($this->superAdmin())
            ->get('/admin/role-access')
            ->assertOk()
            ->assertSee('name="_token"', false);
    }

    public function test_non_grantable_keys_are_rejected(): void
    {
        $sa = $this->superAdmin();
        $financeId = DB::table('Roles')->insertGetId([
            'RoleName' => 'Finance',
            'RoleDescription' => 'Finance',
        ]);

        $this->actingAs($sa)
            ->post('/admin/role-access', [
                'role_id' => $financeId,
                'auth_version' => app(PermissionService::class)->version(),
                'password' => 'secret',
                'keys' => ['web.admin.passwords'],
            ])
            ->assertSessionHasErrors('keys');
    }

    public function test_superadmin_role_cannot_be_edited_in_the_matrix(): void
    {
        $sa = $this->superAdmin();
        $saRoleId = (int) DB::table('Roles')->where('RoleName', 'SuperAdmin')->value('RoleID');

        $this->actingAs($sa)
            ->post('/admin/role-access', [
                'role_id' => $saRoleId,
                'auth_version' => app(PermissionService::class)->version(),
                'password' => 'secret',
                'keys' => ['web.finance.manage'],
            ])
            ->assertSessionHasErrors('role_id');
    }

    private function superAdmin(): User
    {
        $user = User::create([
            'FirstName' => 'Root',
            'SecondName' => 'Admin',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'SA'.uniqid(),
        ]);

        $roleId = DB::table('Roles')->insertGetId([
            'RoleName' => 'SuperAdmin',
            'RoleDescription' => 'SuperAdmin',
        ]);

        DB::table('PersonRole')->insert([
            'PersonID' => $user->PersonID,
            'RoleID' => $roleId,
        ]);

        DB::table('PersonSystemPassword')->insert([
            'PersonID' => $user->PersonID,
            'Password' => Hash::make('secret'),
        ]);

        return $user->fresh();
    }
}
