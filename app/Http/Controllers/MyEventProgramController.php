<?php

namespace App\Http\Controllers;

use App\Domain\EventProgram\CampEventTypeGate;
use App\Domain\EventProgram\EventProgramQuery;
use App\Domain\Person\AuthenticatedPersonId;
use Illuminate\Http\Request;

class MyEventProgramController extends Controller
{
    public function __construct(
        private readonly EventProgramQuery $query,
        private readonly CampEventTypeGate $gate,
    ) {}

    public function index(Request $request)
    {
        $programs = $this->query->listForPerson(AuthenticatedPersonId::from($request));

        return view('my_program.index', compact('programs'));
    }

    public function show(Request $request, int $seasonEventId)
    {
        $payload = $this->query->myProgramPayload($seasonEventId, AuthenticatedPersonId::from($request));
        if (! $payload) {
            return redirect()->route('my-program.index')
                ->with('error', 'لا يوجد برنامج منشور لك في هذا المعسكر.');
        }
        $meta = $this->gate->seasonEventMeta($seasonEventId);

        return view('my_program.show', [
            'program' => $payload['program'],
            'days' => $payload['days'],
            'meta' => $meta,
            'seasonEventId' => $seasonEventId,
        ]);
    }

    public function day(Request $request, int $seasonEventId, int $dayNumber)
    {
        $payload = $this->query->myProgramPayload($seasonEventId, AuthenticatedPersonId::from($request));
        if (! $payload) {
            return redirect()->route('my-program.index')
                ->with('error', 'لا يوجد برنامج منشور لك في هذا المعسكر.');
        }
        $day = collect($payload['days'])->firstWhere('day_number', $dayNumber);
        if (! $day) {
            return redirect()->route('my-program.show', $seasonEventId)
                ->with('error', 'هذا اليوم غير موجود في برنامجك.');
        }
        $meta = $this->gate->seasonEventMeta($seasonEventId);

        return view('my_program.day', [
            'program' => $payload['program'],
            'day' => $day,
            'days' => $payload['days'],
            'meta' => $meta,
            'seasonEventId' => $seasonEventId,
        ]);
    }
}
