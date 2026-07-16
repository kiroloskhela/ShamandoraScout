<?php

namespace App\Domain\Person;

use Illuminate\Http\Request;

/**
 * Self-only API identity: always the authenticated PersonID (IDOR decision A).
 */
class AuthenticatedPersonId
{
    public static function from(Request $request): int
    {
        $user = $request->user();
        if (! $user) {
            return 0;
        }

        return (int) ($user->PersonID ?? $user->getAuthIdentifier());
    }
}
