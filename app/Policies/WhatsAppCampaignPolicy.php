<?php

namespace App\Policies;

use App\Domain\Authz\PermissionService;
use App\Models\User;
use App\Models\WhatsAppCampaign;

/**
 * WhatsApp campaigns: SuperAdmin only.
 */
class WhatsAppCampaignPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function view(User $user, WhatsAppCampaign $campaign): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function update(User $user, WhatsAppCampaign $campaign): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function manage(User $user, WhatsAppCampaign $campaign): bool
    {
        return $this->isSuperAdmin($user);
    }

    private function isSuperAdmin(User $user): bool
    {
        return app(PermissionService::class)->isSuperAdmin($user);
    }
}
