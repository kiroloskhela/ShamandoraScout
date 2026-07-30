<?php

namespace App\Http\Controllers;

use App\Domain\EventProgram\CampEventTypeGate;
use App\Domain\EventProgram\EventProgramAdminService;
use App\Domain\EventProgram\EventProgramImporter;
use App\Domain\EventProgram\EventProgramQuery;
use App\Domain\EventProgram\EventProgramWhatsAppService;
use App\Domain\EventProgram\EventProgramRefreshService;
use App\Domain\EventProgram\GoogleSheetFetcher;
use App\Domain\EventProgram\GuideTemplateBuilder;
use App\Models\EventProgram;
use App\Models\EventProgramDay;
use App\Models\EventProgramImportSession;
use App\Models\EventProgramResource;
use App\Models\EventProgramSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

class EventProgramController extends Controller
{
    public function __construct(
        private readonly CampEventTypeGate $gate,
        private readonly EventProgramAdminService $admin,
        private readonly EventProgramQuery $query,
        private readonly EventProgramImporter $importer,
        private readonly EventProgramRefreshService $refresh,
        private readonly GoogleSheetFetcher $sheets,
        private readonly GuideTemplateBuilder $guide,
        private readonly EventProgramWhatsAppService $whatsapp,
    ) {}

    public function index()
    {
        $types = CampEventTypeGate::allowedTypes();
        $events = DB::table('SeasonEvent as se')
            ->join('Event as e', 'se.EventID', '=', 'e.EventID')
            ->join('EventType as et', 'e.EventTypeID', '=', 'et.EventTypeID')
            ->join('Season as s', 'se.SeasonID', '=', 's.SeasonID')
            ->leftJoin('event_programs as p', 'p.SeasonEventID', '=', 'se.SeasonEventID')
            ->whereIn('et.EventTypeName', $types)
            ->orderByDesc('e.EventStartDate')
            ->select([
                'se.SeasonEventID',
                'e.EventName',
                'e.EventStartDate',
                'e.EventEndDate',
                'et.EventTypeName',
                's.SeasonName',
                's.SeasonYear',
                'p.id as program_id',
                'p.status as program_status',
                'p.title as program_title',
                'p.source_url as program_source_url',
                'p.last_refreshed_at as program_last_refreshed_at',
            ])
            ->get();

        return view('event_program.index', compact('events'));
    }

    public function open(int $seasonEventId)
    {
        try {
            $program = $this->admin->createOrGet($seasonEventId);
        } catch (Throwable $e) {
            return redirect()->route('event-program.index')->with('error', $e->getMessage());
        }

        return redirect()->route('event-program.show', $program->id);
    }

    public function show(int $id)
    {
        $program = EventProgram::with(['days.slots.assignments', 'resources'])->findOrFail($id);
        $meta = $this->gate->seasonEventMeta((int) $program->SeasonEventID);
        $sessions = EventProgramImportSession::query()
            ->where('SeasonEventID', $program->SeasonEventID)
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $personNames = [];
        $personIds = collect($program->days)
            ->flatMap(fn ($d) => $d->slots->flatMap(fn ($s) => $s->assignments->pluck('person_id')))
            ->unique()
            ->values()
            ->all();
        if ($personIds !== []) {
            $personNames = DB::table('PersonInformation')
                ->whereIn('PersonID', $personIds)
                ->get()
                ->mapWithKeys(function ($p) {
                    $name = trim(implode(' ', array_filter([
                        $p->FirstName ?? null,
                        $p->SecondName ?? null,
                        $p->ThirdName ?? null,
                    ])));

                    return [(int) $p->PersonID => $name];
                })
                ->all();
        }

        return view('event_program.show', compact('program', 'meta', 'sessions', 'personNames'));
    }

    public function downloadGuide()
    {
        return $this->guide->downloadResponse();
    }

    public function updateMeta(Request $request, int $id)
    {
        $program = EventProgram::findOrFail($id);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'intro_template' => 'nullable|string',
            'outro_template' => 'nullable|string',
            'source_url' => 'nullable|url|max:1000',
        ]);
        $program->fill($data)->save();

        return back()->with('success', 'تم حفظ بيانات البرنامج.');
    }

    public function publish(int $id)
    {
        try {
            $this->admin->publish(EventProgram::findOrFail($id));
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم نشر البرنامج للقادة.');
    }

    public function unpublish(int $id)
    {
        $this->admin->unpublish(EventProgram::findOrFail($id));

        return back()->with('success', 'تم إرجاع البرنامج لمسودة.');
    }

    public function storeDay(Request $request, int $id)
    {
        $program = EventProgram::findOrFail($id);
        $data = $request->validate([
            'day_number' => 'required|integer|min:1|max:30',
            'label' => 'nullable|string|max:120',
            'date' => 'nullable|date',
        ]);
        try {
            $this->admin->addDay($program, $data);
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تمت إضافة اليوم.');
    }

    public function storeSlot(Request $request, int $dayId)
    {
        $day = EventProgramDay::findOrFail($dayId);
        $data = $request->validate([
            'start_time' => 'required',
            'end_time' => 'required',
            'activity_label' => 'required|string|max:255',
            'slot_kind' => 'nullable|in:general,games,lecture,duty,other',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $this->admin->addSlot($day, $data);

        return back()->with('success', 'تمت إضافة الفقرة.');
    }

    public function storeAssignment(Request $request, int $slotId)
    {
        $slot = EventProgramSlot::findOrFail($slotId);
        $data = $request->validate([
            'person_id' => 'required|integer',
            'mission_text' => 'nullable|string|max:255',
            'team_label' => 'nullable|string|max:120',
        ]);
        $this->admin->upsertAssignment($slot, $data);

        return back()->with('success', 'تم حفظ المهمة.');
    }

    public function storeResource(Request $request, int $id)
    {
        $program = EventProgram::findOrFail($id);
        $data = $request->validate([
            'kind' => 'required|in:game,lecture',
            'title' => 'required|string|max:255',
            'url' => 'nullable|url|max:1000',
            'day_id' => 'nullable|integer',
            'slot_label' => 'nullable|string|max:120',
        ]);
        $this->admin->addResource($program, $data);

        return back()->with('success', 'تمت إضافة المورد.');
    }

    public function destroyResource(int $resourceId)
    {
        EventProgramResource::where('id', $resourceId)->delete();

        return back()->with('success', 'تم حذف المورد.');
    }

    public function importForm(int $id)
    {
        $program = EventProgram::findOrFail($id);

        return view('event_program.import', compact('program'));
    }

    public function import(Request $request, int $id)
    {
        $program = EventProgram::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'file' => 'nullable|file|mimes:xlsx,csv,txt,zip',
            'google_url' => 'nullable|url',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        if (! $request->hasFile('file') && ! $request->filled('google_url')) {
            return back()->with('error', 'ارفع ملف أو الصق رابط Google Sheets.');
        }

        try {
            $createdBy = (int) (Auth::id() ?: 0);
            if ($request->filled('google_url')) {
                $tmpDir = storage_path('app/tmp');
                if (! is_dir($tmpDir)) {
                    mkdir($tmpDir, 0775, true);
                }
                $tmp = $tmpDir.'/event_program_'.uniqid('', true).'.xlsx';
                $googleUrl = (string) $request->input('google_url');
                $this->sheets->downloadXlsx($googleUrl, $tmp);
                $session = $this->importer->startFromXlsx(
                    (int) $program->SeasonEventID,
                    $tmp,
                    $createdBy,
                    'google'
                );
                @unlink($tmp);
                $program->source_url = $googleUrl;
                $program->save();
            } else {
                $file = $request->file('file');
                $ext = strtolower($file->getClientOriginalExtension());
                $stored = $file->storeAs('tmp', 'event_program_'.uniqid('', true).'.'.$ext);
                $path = storage_path('app/'.$stored);
                if ($ext === 'csv') {
                    $session = $this->importer->startFromCsvPack(
                        (int) $program->SeasonEventID,
                        ['Day 1' => $path, 'Meta' => $path],
                        $createdBy,
                        'upload'
                    );
                } else {
                    $session = $this->importer->startFromXlsx(
                        (int) $program->SeasonEventID,
                        $path,
                        $createdBy,
                        'upload'
                    );
                }
            }
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($session->status === EventProgramImportSession::STATUS_READY) {
            $this->importer->commit($session, [
                'source_url' => $program->source_url,
            ]);

            return redirect()->route('event-program.show', $program->id)
                ->with('success', 'تم الاستيراد بنجاح.');
        }

        return redirect()->route('event-program.import.review', $session->id);
    }

    public function refresh(Request $request, int $id)
    {
        $program = EventProgram::findOrFail($id);
        $url = $request->input('google_url') ?: $program->source_url;

        try {
            $result = $this->refresh->refreshFromSource(
                $program,
                (int) (Auth::id() ?: 0),
                is_string($url) ? $url : null,
            );
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($result['needs_review']) {
            return redirect()
                ->route('event-program.import.review', $result['session']->id)
                ->with('success', $result['summary']);
        }

        return redirect()
            ->route('event-program.show', $result['program']->id)
            ->with('success', $result['summary']);
    }

    public function review(int $sessionId)
    {
        $session = EventProgramImportSession::findOrFail($sessionId);
        $program = EventProgram::query()->where('SeasonEventID', $session->SeasonEventID)->first();

        return view('event_program.review', compact('session', 'program'));
    }

    public function answer(Request $request, int $sessionId)
    {
        $session = EventProgramImportSession::findOrFail($sessionId);
        $answers = $request->input('answers', []);
        if (! is_array($answers)) {
            $answers = [];
        }

        // Auto-skip unanswered person questions when operator chooses "import matched only".
        if ($request->boolean('skip_unresolved')) {
            foreach ($session->questions_json ?? [] as $q) {
                $id = $q['id'] ?? null;
                if (! $id || array_key_exists($id, $answers)) {
                    continue;
                }
                if (($q['code'] ?? '') === 'person_unresolved') {
                    $answers[$id] = 'skip';
                } elseif (($q['code'] ?? '') === 'resource_missing_url') {
                    $answers[$id] = 'continue';
                } elseif (($q['code'] ?? '') === 'resource_same_title_multi_day') {
                    $answers[$id] = 'different';
                }
            }
        }

        $this->importer->answer($session, $answers);
        try {
            $program = EventProgram::query()->where('SeasonEventID', $session->SeasonEventID)->first();
            $preserve = ($session->source === 'refresh' && $program)
                ? $program->status
                : null;
            $program = $this->importer->commit($session->fresh(), [
                'preserve_status' => $preserve,
                'source_url' => $program?->source_url,
                'is_refresh' => $session->source === 'refresh',
            ]);
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('event-program.show', $program->id)
            ->with('success', 'تم حفظ الإجابات واعتماد البرنامج.');
    }

    public function sendWhatsApp(Request $request, int $id)
    {
        $program = EventProgram::with('days')->findOrFail($id);
        $dayNumber = $request->filled('day_number') ? (int) $request->input('day_number') : null;
        try {
            $campaign = $this->whatsapp->createCampaignDraft(
                $program,
                (int) (Auth::id() ?: 0),
                $dayNumber
            );
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('whatsapp.campaigns.show', $campaign->id)
            ->with('success', 'تم إنشاء مسودة حملة واتساب — راجعها ثم أكّد الإرسال.');
    }
}
