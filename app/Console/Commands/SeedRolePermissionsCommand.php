<?php

namespace App\Console\Commands;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Console\Command;

class SeedRolePermissionsCommand extends Command
{
    protected $signature = 'permissions:seed';

    protected $description = 'Additively seed role_permissions from config/permissions.php (never deletes).';

    public function handle(): int
    {
        (new RolePermissionSeeder)->run();
        $this->info('Role permissions seeded.');

        return self::SUCCESS;
    }
}
