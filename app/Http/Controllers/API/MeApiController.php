<?php

namespace App\Http\Controllers\API;

use App\Domain\Authz\PermissionService;
use App\Domain\Person\AuthenticatedPersonId;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeApiController extends Controller
{
    public function show(Request $request, PermissionService $permissions): JsonResponse
    {
        $user = $request->user();
        $personId = AuthenticatedPersonId::from($request);

        return response()->json([
            'ok' => true,
            'person_id' => $personId,
            'display_name' => trim(implode(' ', array_filter([
                $user?->FirstName,
                $user?->SecondName,
                $user?->ThirdName,
            ]))),
            'role_names' => $user?->role()->pluck('RoleName')->values() ?? [],
            'permissions' => $permissions->clientKeysForUser($user),
        ]);
    }
}
