<?php

namespace App\Policies;

use App\Domain\Authz\PermissionService;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Custody requests: owners manage their pending requests; inventory admins review.
 */
class CustodyPolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function view(User $user, int $requestId): bool
    {
        if ($this->isInventoryStaff($user)) {
            return $this->requestExists($requestId);
        }

        return $this->ownsRequest($user, $requestId);
    }

    public function update(User $user, int $requestId): bool
    {
        return $this->ownsPendingRequest($user, $requestId);
    }

    public function delete(User $user, int $requestId): bool
    {
        return $this->ownsPendingRequest($user, $requestId);
    }

    public function viewAdmin(User $user): bool
    {
        return app(PermissionService::class)->userCan($user, 'web.inventory.manage');
    }

    public function review(User $user): bool
    {
        return app(PermissionService::class)->userCan($user, 'web.inventory.review');
    }

    private function isInventoryStaff(User $user): bool
    {
        $p = app(PermissionService::class);

        return $p->userCan($user, 'web.inventory.manage') || $p->userCan($user, 'web.inventory.review');
    }

    private function requestExists(int $requestId): bool
    {
        return DB::table('CustodyRequests')->where('RequestID', $requestId)->exists();
    }

    private function ownsRequest(User $user, int $requestId): bool
    {
        return DB::table('CustodyRequests')
            ->where('RequestID', $requestId)
            ->where('PersonID', $user->PersonID)
            ->exists();
    }

    private function ownsPendingRequest(User $user, int $requestId): bool
    {
        return DB::table('CustodyRequests')
            ->where('RequestID', $requestId)
            ->where('PersonID', $user->PersonID)
            ->where('Status', 'pending')
            ->exists();
    }
}
