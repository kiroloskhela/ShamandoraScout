<?php

namespace App\Domain\Auth;

use App\Domain\Authz\PermissionService;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class TokenSessionService
{
    public const FAMILY_PREFIX = 'family:';

    public function issue(User $user, Request $request): array
    {
        $familyId = (string) Str::uuid();
        $plainRefresh = Str::random(64);

        RefreshToken::create([
            'user_id' => $user->PersonID,
            'token_hash' => hash('sha256', $plainRefresh),
            'family_id' => $familyId,
            'expires_at' => now()->addDays(30),
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);

        $access = $user->createToken(self::FAMILY_PREFIX.$familyId, ['*'], now()->addHour())->plainTextToken;

        return [
            'access_token' => $access,
            'refresh_token' => $plainRefresh,
            'expires_in_sec' => 3600,
        ];
    }

    public function refresh(string $plain): ?array
    {
        return DB::transaction(function () use ($plain) {
            $row = RefreshToken::where('token_hash', hash('sha256', $plain))
                ->lockForUpdate()
                ->first();

            if (! $row) {
                return null;
            }

            if ($row->revoked_at) {
                if ($row->family_id) {
                    $this->revokeFamily((int) $row->user_id, (string) $row->family_id);
                }

                return null;
            }

            if ($row->expires_at?->isPast()) {
                return null;
            }

            $user = $row->user;
            if (! $user || ! app(PermissionService::class)->hasAppAccess($user)) {
                $this->revokeAllForUser((int) $row->user_id);

                return null;
            }

            $familyId = $row->family_id ?: (string) Str::uuid();
            if (! $row->family_id) {
                $row->family_id = $familyId;
                $row->save();
            }

            $access = $user->createToken(self::FAMILY_PREFIX.$familyId, ['*'], now()->addHour())->plainTextToken;

            return [
                'access_token' => $access,
                'refresh_token' => $plain,
                'expires_in_sec' => 3600,
                'user' => $user,
            ];
        });
    }

    public function revokeFamily(int $userId, string $familyId): void
    {
        DB::transaction(function () use ($userId, $familyId) {
            RefreshToken::where('user_id', $userId)
                ->where('family_id', $familyId)
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);

            User::find($userId)?->tokens()->where('name', self::FAMILY_PREFIX.$familyId)->delete();
        });
    }

    public function revokeAllForUser(int $userId): void
    {
        DB::transaction(function () use ($userId) {
            RefreshToken::where('user_id', $userId)
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);

            User::find($userId)?->tokens()->delete();
        });
    }

    public function logoutCurrent(User $user, mixed $token): void
    {
        $userId = (int) $user->PersonID;

        if ($token instanceof PersonalAccessToken && str_starts_with((string) $token->name, self::FAMILY_PREFIX)) {
            $this->revokeFamily($userId, substr((string) $token->name, strlen(self::FAMILY_PREFIX)));

            return;
        }

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        RefreshToken::where('user_id', $userId)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }

    public function revokeIfNoAppAccess(int $personId): void
    {
        $user = User::find($personId);
        if (! $user || ! app(PermissionService::class)->hasAppAccess($user)) {
            $this->revokeAllForUser($personId);
        }
    }
}
