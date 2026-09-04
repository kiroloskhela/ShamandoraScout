<?php

namespace App\Http\Controllers;

use App\Domain\EventFinance\FinancePlanIntervals;
use App\Support\WholeNumberInput;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SeasonEventFinanceController extends Controller
{
    public function __construct(private readonly FinancePlanIntervals $planIntervals) {}

    public function index(Request $request)
    {
        $finance = DB::table('SeasonEventFinance as sef')
            ->join('SeasonEvent as se', 'sef.SeasonEventID', '=', 'se.SeasonEventID')
            ->join('Season as s', 'se.SeasonID', '=', 's.SeasonID')
            ->join('Event as e', 'se.EventID', '=', 'e.EventID')
            ->join('EventType as et', 'e.EventTypeID', '=', 'et.EventTypeID')
            ->leftJoin('SeasonEventFinancePrice as sefp', 'sef.SeasonEventID', '=', 'sefp.SeasonEventID')
            ->select(
                'sef.SeasonEventID',
                's.SeasonName',
                's.SeasonYear',
                'e.EventStartDate',
                'e.EventEndDate',
                'sef.MaxInstallmentsNumber',
                'sef.MinimumDeposit',
                'sef.AllowBelowMinimumDeposit',
                DB::raw("CONCAT(et.EventTypeName, ' - ', e.EventName) as EventDisplayName"),
                DB::raw('COUNT(sefp.SeasonEventFinancePriceID) as IntervalsCount'),

            )
            ->groupBy(
                'sef.SeasonEventID',
                's.SeasonName',
                's.SeasonYear',
                'e.EventStartDate',
                'e.EventEndDate',
                'sef.MaxInstallmentsNumber',
                'sef.MinimumDeposit',
                'sef.AllowBelowMinimumDeposit',
                'et.EventTypeName',
                'e.EventName'
            )
            ->orderByDesc('s.SeasonYear')
            ->orderBy('s.SeasonName')
            ->orderBy('e.EventStartDate')
            ->get();

        $paidSeasonEventIds = [];
        $seasonEventIds = $finance->pluck('SeasonEventID')->all();
        if ($seasonEventIds !== []) {
            $paidSeasonEventIds = DB::table('SeasonEventParticipantFinance as sepf')
                ->join('SeasonEventParticipantFinancePayment as p', 'sepf.SeasonEventParticipantFinanceID', '=', 'p.SeasonEventParticipantFinanceID')
                ->whereIn('sepf.SeasonEventID', $seasonEventIds)
                ->distinct()
                ->pluck('sepf.SeasonEventID')
                ->all();
        }
        $paidSet = array_fill_keys($paidSeasonEventIds, true);

        $finance = $finance->map(function ($row) use ($paidSet) {
            $row->AllowBelowMinimumDepositText = $row->AllowBelowMinimumDeposit ? 'نعم' : 'لا';
            $row->CanEditDelete = ! isset($paidSet[$row->SeasonEventID]);
            $row->CanEditDeleteText = $row->CanEditDelete ? 'نعم' : 'لا، يوجد مدفوعات';

            return $row;
        });

        return view('finance.index', ['finance' => $finance]);
    }

    public function create()
    {
        $seasons = DB::table('Season')->orderBy('SeasonYear', 'desc')->get();

        return view('finance.create', compact('seasons'));
    }

    public function getEventsForSeason(Request $request)
    {
        $seasonID = $request->query('seasonID');

        if (! $seasonID) {
            return response()->json([]);
        }

        $events = DB::table('SeasonEvent as se')
            ->join('Event as e', 'se.EventID', '=', 'e.EventID')
            ->join('EventType as et', 'e.EventTypeID', '=', 'et.EventTypeID')
            ->leftJoin('SeasonEventFinance as sef', 'se.SeasonEventID', '=', 'sef.SeasonEventID')
            ->where('se.SeasonID', $seasonID)
            ->where('et.TakesReservation', 1)
            ->select(
                'se.SeasonEventID',
                'se.EventID',
                'e.EventName',
                'e.EventStartDate',
                'e.EventEndDate',
                DB::raw('CASE WHEN sef.SeasonEventID IS NULL THEN 0 ELSE 1 END as HasFinancePlan')
            )
            ->orderBy('e.EventStartDate')
            ->get();

        $sectorsByEvent = $this->planIntervals->sectorsByEvent(
            $events->pluck('EventID')->map(fn ($id) => (int) $id)->unique()->values()->all()
        );

        $events = $events->map(function ($event) use ($sectorsByEvent) {
            $event->Sectors = $this->sectorOptions($sectorsByEvent[(int) $event->EventID] ?? []);

            return $event;
        });

        return response()->json($events);
    }

    public function store(Request $request)
    {
        $this->coerceWholeNumberFinanceInputs($request);

        $validator = Validator::make($request->all(), [
            'season_event_id' => 'required|integer|exists:SeasonEvent,SeasonEventID',
            ...$this->planRules(),
        ], [
            'season_event_id.required' => __('Event is required.'),
            'season_event_id.exists' => __('Selected event does not exist.'),
            ...$this->planMessages(),
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $seasonEventID = $request->season_event_id;

        $existingPlan = DB::table('SeasonEventFinance')
            ->where('SeasonEventID', $seasonEventID)
            ->exists();

        if ($existingPlan) {
            return redirect()->back()->withErrors([
                'season_event_id' => __('This event already has a finance plan.'),
            ])->withInput();
        }

        $event = $this->getSeasonEventDetails($seasonEventID);
        if (! $event) {
            return redirect()->back()->withErrors([
                'season_event_id' => __('Could not find event data.'),
            ])->withInput();
        }

        $takesReservation = DB::table('SeasonEvent as se')
            ->join('Event as e', 'se.EventID', '=', 'e.EventID')
            ->join('EventType as et', 'e.EventTypeID', '=', 'et.EventTypeID')
            ->where('se.SeasonEventID', $seasonEventID)
            ->value('et.TakesReservation');

        if (empty($takesReservation)) {
            return redirect()->back()->withErrors([
                'season_event_id' => __('Finance plans can only be created for events that accept bookings.'),
            ])->withInput();
        }

        $intervalsResult = $this->planIntervals->prepare(
            $request->input('intervals'),
            $this->planIntervals->eventSectors((int) $seasonEventID),
            $event->EventStartDate
        );

        if (! $intervalsResult['success']) {
            return redirect()->back()->withErrors([
                'intervals' => $intervalsResult['message'],
            ])->withInput();
        }

        DB::beginTransaction();

        try {
            DB::table('SeasonEventFinance')->insert([
                'SeasonEventID' => $seasonEventID,
                'MaxInstallmentsNumber' => $request->max_installments_number,
                'MinimumDeposit' => $request->minimum_deposit,
                'AllowBelowMinimumDeposit' => $request->allow_below_minimum_deposit,
                'HaveShirt' => $request->have_shirt,
                'SendQrWhatsApp' => $request->send_qr_whatsapp,
            ]);

            $this->planIntervals->replace((int) $seasonEventID, $intervalsResult['intervals']);

            DB::commit();

            return redirect()->route('finance.index')->with('success', __('Finance plan added successfully.'));
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->withErrors([
                'general' => __('An error occurred while saving the finance plan.'),
            ])->withInput();
        }
    }

    public function edit($id)
    {
        $finance = DB::table('SeasonEventFinance as sef')
            ->join('SeasonEvent as se', 'sef.SeasonEventID', '=', 'se.SeasonEventID')
            ->join('Season as s', 'se.SeasonID', '=', 's.SeasonID')
            ->join('Event as e', 'se.EventID', '=', 'e.EventID')
            ->where('sef.SeasonEventID', $id)
            ->select(
                'sef.SeasonEventID',
                'sef.MaxInstallmentsNumber',
                'sef.MinimumDeposit',
                'sef.AllowBelowMinimumDeposit',
                'sef.HaveShirt',
                'sef.SendQrWhatsApp',
                's.SeasonName',
                's.SeasonYear',
                'e.EventName',
                'e.EventStartDate',
                'e.EventEndDate'
            )
            ->first();

        if (! $finance) {
            abort(404);
        }

        if ($this->hasPayments($id)) {
            return redirect()->route('finance.index')->withErrors([
                'general' => __('This plan cannot be edited because it has linked payments.'),
            ]);
        }

        $intervals = $this->planIntervals->forEdit((int) $id);
        $sectors = $this->sectorOptions($this->planIntervals->eventSectors((int) $id));

        return view('finance.edit', compact('finance', 'intervals', 'sectors'));
    }

    public function update(Request $request, $id)
    {
        $financeExists = DB::table('SeasonEventFinance')
            ->where('SeasonEventID', $id)
            ->exists();

        if (! $financeExists) {
            abort(404);
        }

        if ($this->hasPayments($id)) {
            return redirect()->route('finance.index')->withErrors([
                'general' => __('This plan cannot be edited because it has linked payments.'),
            ]);
        }

        $this->coerceWholeNumberFinanceInputs($request);

        $validator = Validator::make($request->all(), $this->planRules(), $this->planMessages());

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $event = $this->getSeasonEventDetails($id);
        if (! $event) {
            return redirect()->back()->withErrors([
                'general' => __('Could not find event data.'),
            ])->withInput();
        }

        $intervalsResult = $this->planIntervals->prepare(
            $request->input('intervals'),
            $this->planIntervals->eventSectors((int) $id),
            $event->EventStartDate
        );

        if (! $intervalsResult['success']) {
            return redirect()->back()->withErrors([
                'intervals' => $intervalsResult['message'],
            ])->withInput();
        }

        DB::beginTransaction();

        try {
            DB::table('SeasonEventFinance')
                ->where('SeasonEventID', $id)
                ->update([
                    'MaxInstallmentsNumber' => $request->max_installments_number,
                    'MinimumDeposit' => $request->minimum_deposit,
                    'AllowBelowMinimumDeposit' => $request->allow_below_minimum_deposit,
                    'HaveShirt' => $request->have_shirt,
                    'SendQrWhatsApp' => $request->send_qr_whatsapp,
                ]);

            $this->planIntervals->replace((int) $id, $intervalsResult['intervals']);

            DB::commit();

            return redirect()->route('finance.index')->with('success', __('Finance plan updated successfully.'));
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->withErrors([
                'general' => __('An error occurred while updating the finance plan.'),
            ])->withInput();
        }
    }

    public function delete($id)
    {
        $finance = DB::table('SeasonEventFinance as sef')
            ->join('SeasonEvent as se', 'sef.SeasonEventID', '=', 'se.SeasonEventID')
            ->join('Season as s', 'se.SeasonID', '=', 's.SeasonID')
            ->join('Event as e', 'se.EventID', '=', 'e.EventID')
            ->where('sef.SeasonEventID', $id)
            ->select(
                'sef.SeasonEventID',
                's.SeasonName',
                's.SeasonYear',
                'e.EventName',
                'e.EventStartDate',
                'e.EventEndDate'
            )
            ->first();

        if (! $finance) {
            abort(404);
        }

        if ($this->hasPayments($id)) {
            return redirect()->route('finance.index')->withErrors([
                'general' => __('This plan cannot be deleted because it has linked payments.'),
            ]);
        }

        return view('finance.delete', compact('finance'));
    }

    public function destroy($id)
    {
        $financeExists = DB::table('SeasonEventFinance')
            ->where('SeasonEventID', $id)
            ->exists();

        if (! $financeExists) {
            abort(404);
        }

        if ($this->hasPayments($id)) {
            return redirect()->route('finance.index')->withErrors([
                'general' => __('This plan cannot be deleted because it has linked payments.'),
            ]);
        }

        DB::beginTransaction();

        try {
            $this->planIntervals->deleteAll((int) $id);

            DB::table('SeasonEventFinance')
                ->where('SeasonEventID', $id)
                ->delete();

            DB::commit();

            return redirect()->route('finance.index')->with('success', __('Finance plan deleted successfully.'));
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->route('finance.index')->withErrors([
                'general' => __('An error occurred while deleting the finance plan.'),
            ]);
        }
    }

    private function coerceWholeNumberFinanceInputs(Request $request): void
    {
        $merge = [];

        foreach (['max_installments_number', 'minimum_deposit'] as $key) {
            if ($request->exists($key)) {
                $merge[$key] = WholeNumberInput::coerce($request->input($key));
            }
        }

        $intervals = $request->input('intervals');
        if (is_array($intervals)) {
            foreach ($intervals as $key => $row) {
                if (is_array($row) && array_key_exists('price', $row)) {
                    $intervals[$key]['price'] = WholeNumberInput::coerce($row['price']);
                }
            }
            $merge['intervals'] = $intervals;
        }

        if ($merge !== []) {
            $request->merge($merge);
        }
    }

    /**
     * @return array<string, string>
     */
    private function planRules(): array
    {
        return [
            'max_installments_number' => 'required|integer|min:1',
            'minimum_deposit' => 'required|integer|min:0',
            'allow_below_minimum_deposit' => 'required|in:0,1',
            'have_shirt' => 'required|in:0,1',
            'send_qr_whatsapp' => 'required|in:0,1',
            'intervals' => 'required|array|min:1|max:'.FinancePlanIntervals::MAX_ROWS,
            'intervals.*.start_date' => 'required|date',
            'intervals.*.end_date' => 'required|date',
            'intervals.*.price' => 'required|integer|min:0',
            'intervals.*.audience' => 'required|array|min:1|max:100',
            'intervals.*.audience.*' => 'required|string|max:20',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function planMessages(): array
    {
        return [
            'max_installments_number.required' => __('Maximum number of installments is required.'),
            'max_installments_number.integer' => __('Number of installments must be a whole number.'),
            'max_installments_number.min' => __('Number of installments must be at least 1.'),
            'minimum_deposit.required' => __('Minimum deposit is required.'),
            'minimum_deposit.integer' => __('Minimum deposit must be a whole number without cents.'),
            'minimum_deposit.min' => __('Minimum deposit cannot be less than 0.'),
            'have_shirt.required' => __('You must specify whether a shirt is included.'),
            'have_shirt.in' => __('Invalid shirt value.'),
            'send_qr_whatsapp.required' => __('You must specify whether to send QR via WhatsApp.'),
            'send_qr_whatsapp.in' => __('Invalid send QR via WhatsApp value.'),
            'intervals.required' => __('At least one price interval is required.'),
            'intervals.max' => __('Too many price intervals (max :max).', ['max' => FinancePlanIntervals::MAX_ROWS]),
            'intervals.*.start_date.required' => __('All price interval fields must be filled.'),
            'intervals.*.end_date.required' => __('All price interval fields must be filled.'),
            'intervals.*.price.required' => __('All price interval fields must be filled.'),
            'intervals.*.start_date.date' => __('One of the interval dates is invalid.'),
            'intervals.*.end_date.date' => __('One of the interval dates is invalid.'),
            'intervals.*.price.integer' => __('Price must be a whole number without cents.'),
            'intervals.*.price.min' => __('Price must be a whole number without cents.'),
            'intervals.*.audience.required' => __('Each price interval must apply to at least one sector, families, or guests.'),
            'intervals.*.audience.*.string' => __('Price interval audience is invalid.'),
            'intervals.*.audience.*.max' => __('Price interval audience is invalid.'),
        ];
    }

    /**
     * @param  array<int, string>  $sectors  QetaaID => QetaaName
     * @return list<array{QetaaID: int, QetaaName: string}>
     */
    private function sectorOptions(array $sectors): array
    {
        $options = [];
        foreach ($sectors as $id => $name) {
            $options[] = ['QetaaID' => (int) $id, 'QetaaName' => $name];
        }

        return $options;
    }

    private function hasPayments($seasonEventID)
    {
        return DB::table('SeasonEventParticipantFinance as sepf')
            ->join('SeasonEventParticipantFinancePayment as p', 'sepf.SeasonEventParticipantFinanceID', '=', 'p.SeasonEventParticipantFinanceID')
            ->where('sepf.SeasonEventID', $seasonEventID)
            ->exists();
    }

    private function getSeasonEventDetails($seasonEventID)
    {
        return DB::table('SeasonEvent as se')
            ->join('Season as s', 'se.SeasonID', '=', 's.SeasonID')
            ->join('Event as e', 'se.EventID', '=', 'e.EventID')
            ->where('se.SeasonEventID', $seasonEventID)
            ->select(
                'se.SeasonEventID',
                's.SeasonName',
                's.SeasonYear',
                'e.EventName',
                'e.EventStartDate',
                'e.EventEndDate'
            )
            ->first();
    }
}
