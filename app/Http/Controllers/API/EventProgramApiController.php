<?php

namespace App\Http\Controllers\API;

use App\Domain\EventProgram\EventProgramQuery;
use App\Domain\Person\AuthenticatedPersonId;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventProgramApiController extends Controller
{
    public function __construct(
        private readonly EventProgramQuery $query,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->query->listForPerson(AuthenticatedPersonId::from($request)),
        ]);
    }

    public function show(Request $request, int $seasonEventId): JsonResponse
    {
        $payload = $this->query->myProgramPayload($seasonEventId, AuthenticatedPersonId::from($request));
        if (! $payload) {
            return response()->json(['message' => 'Program not found'], 404);
        }

        return response()->json([
            'data' => [
                'program_id' => $payload['program']->id,
                'title' => $payload['program']->title,
                'SeasonEventID' => $payload['program']->SeasonEventID,
                'days' => $payload['days'],
            ],
        ]);
    }
}
