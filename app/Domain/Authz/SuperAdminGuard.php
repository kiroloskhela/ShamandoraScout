<?php

namespace App\Domain\Authz;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SuperAdminGuard
{
    public const ROLE_NAME = 'SuperAdmin';

    public function assertRoleNameAllowed(string $name): void
    {
        if ($name === self::ROLE_NAME) {
            throw new RuntimeException('The SuperAdmin role name is reserved.');
        }
    }

    public function assertRoleRowMutable(int $roleId, ?string $newName = null): void
    {
        $role = DB::table('Roles')->where('RoleID', $roleId)->first();
        if (! $role) {
            return;
        }

        if ($newName === self::ROLE_NAME && ($role->RoleName ?? '') !== self::ROLE_NAME) {
            throw new RuntimeException('The SuperAdmin role name is reserved.');
        }

        if (($role->RoleName ?? '') !== self::ROLE_NAME) {
            return;
        }

        if ($newName !== null) {
            throw new RuntimeException('The SuperAdmin role cannot be renamed or edited.');
        }

        throw new RuntimeException('The SuperAdmin role cannot be deleted.');
    }

    public function assertPersonRoleChangeAllowed(?int $oldRoleId, ?int $newRoleId, ?User $actor = null): void
    {
        $superAdminRoleId = $this->superAdminRoleId();
        if (! $superAdminRoleId) {
            return;
        }

        $assigning = $newRoleId === $superAdminRoleId && $oldRoleId !== $superAdminRoleId;
        if ($assigning && ! app(PermissionService::class)->isSuperAdmin($actor)) {
            throw new RuntimeException('Only SuperAdmin can assign the SuperAdmin role.');
        }

        $removing = $oldRoleId === $superAdminRoleId && $newRoleId !== $superAdminRoleId;
        if (! $removing) {
            return;
        }

        $this->assertNotLastSuperAdmin();
    }

    public function assertPersonRoleDeleteAllowed(?int $roleId): void
    {
        if ($roleId !== $this->superAdminRoleId()) {
            return;
        }

        $this->assertNotLastSuperAdmin();
    }

    public function superAdminRoleId(): ?int
    {
        $id = DB::table('Roles')->where('RoleName', self::ROLE_NAME)->value('RoleID');

        return $id ? (int) $id : null;
    }

    private function assertNotLastSuperAdmin(): void
    {
        $roleId = $this->superAdminRoleId();
        if (! $roleId) {
            return;
        }

        $count = (int) DB::table('PersonRole')
            ->where('RoleID', $roleId)
            ->lockForUpdate()
            ->distinct()
            ->count('PersonID');
        if ($count <= 1) {
            throw new RuntimeException('The last SuperAdmin assignment cannot be removed.');
        }
    }
}
