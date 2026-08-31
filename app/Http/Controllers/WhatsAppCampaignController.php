<?php

namespace App\Http\Controllers;

use App\Domain\WhatsApp\CampaignRecipientQuery;
use App\Domain\WhatsApp\MessagePersonalizer;
use App\Domain\WhatsApp\WhatsAppCampaignService;
use App\Models\WhatsAppCampaign;
use App\Support\LookupCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WhatsAppCampaignController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', WhatsAppCampaign::class);

        $campaigns = WhatsAppCampaign::query()
            ->withCount('recipients')
            ->orderByDesc('id')
            ->paginate(20);

        return view('whatsapp.campaigns.index', compact('campaigns'));
    }

    public function create(CampaignRecipientQuery $query)
    {
        $this->authorize('create', WhatsAppCampaign::class);

        return view('whatsapp.campaigns.create', $this->formViewData());
    }

    public function createCsv()
    {
        $this->authorize('create', WhatsAppCampaign::class);

        return view('whatsapp.campaigns.create-csv', [
            'highCountThreshold' => WhatsAppCampaignService::HIGH_COUNT_THRESHOLD,
        ]);
    }

    public function downloadCsvTemplate()
    {
        $this->authorize('create', WhatsAppCampaign::class);

        $filename = 'whatsapp-campaign-template.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'wb');
            // UTF-8 BOM for Excel
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Phone Number', 'Message']);
            fputcsv($out, ['1000485402', 'مرحباً، هذه رسالة تجريبية للرقم الأول']);
            fputcsv($out, ['01012345678', 'Hello — custom message for the second number']);
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function storeCsv(Request $request, WhatsAppCampaignService $campaigns)
    {
        $this->authorize('create', WhatsAppCampaign::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
            'min_delay_seconds' => ['nullable', 'integer', 'min:1', 'max:600'],
            'max_delay_seconds' => ['nullable', 'integer', 'min:1', 'max:600'],
            'max_messages_per_hour' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        try {
            $path = $request->file('csv_file')->getRealPath();
            $rows = $campaigns->parseCsvFile((string) $path);
            $campaign = $campaigns->createDraftFromCsvRows($data, $rows, (int) auth()->id());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()
            ->route('whatsapp.campaigns.show', $campaign)
            ->with('success', __('Draft created from CSV (').$campaign->recipients()->count().' رقم). راجع ثم أكّد الإرسال.');
    }

    public function store(Request $request, WhatsAppCampaignService $campaigns)
    {
        $this->authorize('create', WhatsAppCampaign::class);

        $data = $this->validated($request);
        $campaign = $campaigns->createDraft($data, (int) auth()->id());

        return redirect()
            ->route('whatsapp.campaigns.show', $campaign)
            ->with('success', __('Draft saved. Review recipients then confirm send.'));
    }

    public function show(WhatsAppCampaign $campaign, WhatsAppCampaignService $campaigns)
    {
        $this->authorize('view', $campaign);

        $campaign->load(['recipients' => fn ($q) => $q->orderBy('id')]);
        $counts = $campaigns->statusCounts($campaign);

        return view('whatsapp.campaigns.show', [
            'campaign' => $campaign,
            'counts' => $counts,
            'highCountThreshold' => WhatsAppCampaignService::HIGH_COUNT_THRESHOLD,
        ]);
    }

    public function edit(WhatsAppCampaign $campaign)
    {
        $this->authorize('update', $campaign);

        if (! $campaign->isEditable()) {
            return redirect()->route('whatsapp.campaigns.show', $campaign)
                ->with('error', __('Cannot edit a non-draft campaign.'));
        }

        $selectedIds = $campaign->recipients()->pluck('person_id')->all();

        return view('whatsapp.campaigns.edit', array_merge($this->formViewData(), [
            'campaign' => $campaign,
            'selectedIds' => $selectedIds,
        ]));
    }

    public function update(Request $request, WhatsAppCampaign $campaign, WhatsAppCampaignService $campaigns)
    {
        $this->authorize('update', $campaign);

        try {
            $data = $this->validated($request);
            $campaigns->updateDraft($campaign, $data);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()
            ->route('whatsapp.campaigns.show', $campaign)
            ->with('success', __('Draft updated.'));
    }

    public function searchContacts(Request $request, CampaignRecipientQuery $query)
    {
        $this->authorize('create', WhatsAppCampaign::class);

        $filters = [
            'q' => $request->input('q'),
            'gender' => $request->input('gender'),
            'qetaa_ids' => $this->intList($request->input('qetaa_ids', $request->input('qetaa_id'))),
            'group_ids' => $this->intList($request->input('group_ids', $request->input('group_id'))),
            // Campaigns always target WhatsApp-capable numbers only.
            'has_whatsapp' => true,
            'exclude_blocked' => true,
        ];

        $people = $query->search($filters, (int) $request->input('limit', 100));

        return response()->json([
            'ok' => true,
            'count' => $people->count(),
            'people' => $people->map(fn ($p) => [
                'person_id' => (int) $p->PersonID,
                'shamandora_code' => $p->ShamandoraCode,
                'full_name' => $p->full_name,
                'phone' => $p->PersonPersonalMobileNumber,
                'gender' => $p->Gender,
                'qetaa' => $p->QetaaName,
            ])->values(),
        ]);
    }

    public function preview(Request $request, WhatsAppCampaignService $campaigns)
    {
        $this->authorize('create', WhatsAppCampaign::class);

        $data = $request->validate([
            'message_template' => ['required', 'string', 'max:4000'],
            'person_ids' => ['required', 'array', 'min:1'],
            'person_ids.*' => ['integer'],
            'missing_variable_behavior' => ['nullable', 'in:fallback,empty,skip,warn'],
            'fallback_name' => ['nullable', 'string', 'max:120'],
        ]);

        $previews = $campaigns->preview(
            $data['message_template'],
            $data['person_ids'],
            $data['missing_variable_behavior'] ?? 'fallback',
            $data['fallback_name'] ?? 'صديقنا'
        );

        return response()->json([
            'ok' => true,
            'estimated' => count(array_filter($previews, fn ($p) => empty($p['skipped']))),
            'previews' => $previews,
        ]);
    }

    public function confirm(Request $request, WhatsAppCampaign $campaign, WhatsAppCampaignService $campaigns)
    {
        $this->authorize('manage', $campaign);

        try {
            $campaigns->confirmAndStart(
                $campaign,
                $request->boolean('acknowledge_high_count')
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('whatsapp.campaigns.show', $campaign)
            ->with('success', __('Campaign started. Messages are being sent gradually via queue.'));
    }

    public function pause(WhatsAppCampaign $campaign, WhatsAppCampaignService $campaigns)
    {
        $this->authorize('manage', $campaign);

        try {
            $campaigns->pause($campaign);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('Campaign paused.'));
    }

    public function resume(WhatsAppCampaign $campaign, WhatsAppCampaignService $campaigns)
    {
        $this->authorize('manage', $campaign);

        try {
            $campaigns->resume($campaign);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('Campaign resumed.'));
    }

    public function cancel(WhatsAppCampaign $campaign, WhatsAppCampaignService $campaigns)
    {
        $this->authorize('manage', $campaign);

        try {
            $campaigns->cancel($campaign);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('Campaign cancelled.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'message_template' => ['required', 'string', 'max:4000'],
            'missing_variable_behavior' => ['nullable', 'in:fallback,empty,skip,warn'],
            'fallback_name' => ['nullable', 'string', 'max:120'],
            'min_delay_seconds' => ['nullable', 'integer', 'min:1', 'max:600'],
            'max_delay_seconds' => ['nullable', 'integer', 'min:1', 'max:600'],
            'max_messages_per_hour' => ['nullable', 'integer', 'min:1', 'max:500'],
            'person_ids' => ['nullable', 'array'],
            'person_ids.*' => ['integer'],
            'select_all' => ['nullable', 'boolean'],
            'filter_q' => ['nullable', 'string', 'max:120'],
            'filter_gender' => ['nullable', 'string', 'max:20'],
            'filter_qetaa_ids' => ['nullable', 'array'],
            'filter_qetaa_ids.*' => ['integer'],
            'filter_group_ids' => ['nullable', 'array'],
            'filter_group_ids.*' => ['integer'],
        ]);

        if ($request->boolean('select_all')) {
            $data['select_all_filters'] = [
                'q' => $data['filter_q'] ?? null,
                'gender' => $data['filter_gender'] ?? null,
                'qetaa_ids' => $this->intList($data['filter_qetaa_ids'] ?? []),
                'group_ids' => $this->intList($data['filter_group_ids'] ?? []),
                'has_whatsapp' => true,
                'exclude_blocked' => true,
            ];
            unset($data['person_ids']);
        }

        return $data;
    }

    /**
     * @return list<int>
     */
    private function intList(mixed $value): array
    {
        if ($value === null || $value === '' || $value === false) {
            return [];
        }
        if (! is_array($value)) {
            $value = [$value];
        }

        return array_values(array_unique(array_filter(array_map('intval', $value), fn ($id) => $id > 0)));
    }

    /**
     * Shared create/edit form datasets.
     *
     * @return array<string, mixed>
     */
    private function formViewData(): array
    {
        $groups = DB::table('GroupTable as gt')
            ->join('GroupQetaa as gq', 'gq.GroupID', '=', 'gt.GroupID')
            ->select('gt.GroupID', 'gt.GroupName', 'gq.QetaaID')
            ->orderBy('gt.GroupName')
            ->get()
            ->map(fn ($g) => [
                'GroupID' => (int) $g->GroupID,
                'GroupName' => $g->GroupName,
                'QetaaID' => (int) $g->QetaaID,
            ])
            ->values();

        return [
            'qetaat' => LookupCache::ordered('Qetaa', 'QetaaName'),
            'groups' => $groups,
            'variables' => MessagePersonalizer::availableVariables(),
            'highCountThreshold' => WhatsAppCampaignService::HIGH_COUNT_THRESHOLD,
        ];
    }
}
