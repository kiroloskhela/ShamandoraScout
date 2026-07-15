<?php

namespace App\Http\Controllers;

use App\Domain\WhatsApp\CampaignRecipientQuery;
use App\Domain\WhatsApp\MessagePersonalizer;
use App\Domain\WhatsApp\WhatsAppCampaignService;
use App\Models\WhatsAppCampaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WhatsAppCampaignController extends Controller
{
    public function index()
    {
        $campaigns = WhatsAppCampaign::query()
            ->withCount('recipients')
            ->orderByDesc('id')
            ->paginate(20);

        return view('whatsapp.campaigns.index', compact('campaigns'));
    }

    public function create(CampaignRecipientQuery $query)
    {
        return view('whatsapp.campaigns.create', [
            'qetaat' => DB::table('Qetaa')->orderBy('QetaaName')->get(),
            'groups' => DB::table('GroupTable')->orderBy('GroupName')->limit(500)->get(),
            'manteqat' => DB::table('Manteqa')->orderBy('ManteqaName')->get(),
            'districts' => DB::table('Districts')->orderBy('DistrictName')->get(),
            'variables' => MessagePersonalizer::availableVariables(),
            'highCountThreshold' => WhatsAppCampaignService::HIGH_COUNT_THRESHOLD,
        ]);
    }

    public function createCsv()
    {
        return view('whatsapp.campaigns.create-csv', [
            'highCountThreshold' => WhatsAppCampaignService::HIGH_COUNT_THRESHOLD,
        ]);
    }

    public function downloadCsvTemplate()
    {
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
            ->with('success', 'تم إنشاء مسودة من CSV (' . $campaign->recipients()->count() . ' رقم). راجع ثم أكّد الإرسال.');
    }

    public function store(Request $request, WhatsAppCampaignService $campaigns)
    {
        $data = $this->validated($request);
        $campaign = $campaigns->createDraft($data, (int) auth()->id());

        return redirect()
            ->route('whatsapp.campaigns.show', $campaign)
            ->with('success', 'تم حفظ المسودة. راجع المستلمين ثم أكّد الإرسال.');
    }

    public function show(WhatsAppCampaign $campaign, WhatsAppCampaignService $campaigns)
    {
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
        if (!$campaign->isEditable()) {
            return redirect()->route('whatsapp.campaigns.show', $campaign)
                ->with('error', 'لا يمكن تعديل حملة غير مسودة.');
        }

        $selectedIds = $campaign->recipients()->pluck('person_id')->all();

        return view('whatsapp.campaigns.edit', [
            'campaign' => $campaign,
            'selectedIds' => $selectedIds,
            'qetaat' => DB::table('Qetaa')->orderBy('QetaaName')->get(),
            'groups' => DB::table('GroupTable')->orderBy('GroupName')->limit(500)->get(),
            'manteqat' => DB::table('Manteqa')->orderBy('ManteqaName')->get(),
            'districts' => DB::table('Districts')->orderBy('DistrictName')->get(),
            'variables' => MessagePersonalizer::availableVariables(),
            'highCountThreshold' => WhatsAppCampaignService::HIGH_COUNT_THRESHOLD,
        ]);
    }

    public function update(Request $request, WhatsAppCampaign $campaign, WhatsAppCampaignService $campaigns)
    {
        try {
            $data = $this->validated($request);
            $campaigns->updateDraft($campaign, $data);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()
            ->route('whatsapp.campaigns.show', $campaign)
            ->with('success', 'تم تحديث المسودة.');
    }

    public function searchContacts(Request $request, CampaignRecipientQuery $query)
    {
        $filters = [
            'q' => $request->input('q'),
            'gender' => $request->input('gender'),
            'qetaa_id' => $request->integer('qetaa_id') ?: null,
            'group_id' => $request->integer('group_id') ?: null,
            'manteqa_id' => $request->integer('manteqa_id') ?: null,
            'district_id' => $request->integer('district_id') ?: null,
            'has_whatsapp' => $request->has('has_whatsapp')
                ? filter_var($request->input('has_whatsapp'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                : null,
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
            ->with('success', 'بدأت الحملة. الرسائل تُرسل تدريجياً عبر الطابور.');
    }

    public function pause(WhatsAppCampaign $campaign, WhatsAppCampaignService $campaigns)
    {
        try {
            $campaigns->pause($campaign);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم إيقاف الحملة مؤقتاً.');
    }

    public function resume(WhatsAppCampaign $campaign, WhatsAppCampaignService $campaigns)
    {
        try {
            $campaigns->resume($campaign);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم استئناف الحملة.');
    }

    public function cancel(WhatsAppCampaign $campaign, WhatsAppCampaignService $campaigns)
    {
        try {
            $campaigns->cancel($campaign);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم إلغاء الحملة.');
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
            'filter_qetaa_id' => ['nullable', 'integer'],
            'filter_group_id' => ['nullable', 'integer'],
            'filter_manteqa_id' => ['nullable', 'integer'],
            'filter_district_id' => ['nullable', 'integer'],
            'filter_has_whatsapp' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('select_all')) {
            $data['select_all_filters'] = [
                'q' => $data['filter_q'] ?? null,
                'gender' => $data['filter_gender'] ?? null,
                'qetaa_id' => $data['filter_qetaa_id'] ?? null,
                'group_id' => $data['filter_group_id'] ?? null,
                'manteqa_id' => $data['filter_manteqa_id'] ?? null,
                'district_id' => $data['filter_district_id'] ?? null,
                'has_whatsapp' => array_key_exists('filter_has_whatsapp', $data)
                    ? (bool) $data['filter_has_whatsapp']
                    : null,
                'exclude_blocked' => true,
            ];
            unset($data['person_ids']);
        }

        return $data;
    }
}
