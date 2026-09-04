<?php

namespace App\Http\Controllers;

use App\Support\WholeNumberInput;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SeasonEventFinanceController extends Controller
{
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
                'e.EventName',
                'e.EventStartDate',
                'e.EventEndDate',
                DB::raw('CASE WHEN sef.SeasonEventID IS NULL THEN 0 ELSE 1 END as HasFinancePlan')
            )
            ->orderBy('e.EventStartDate')
            ->get();

        return response()->json($events);
    }

    public function store(Request $request)
    {
        $this->coerceWholeNumberFinanceInputs($request);

        $validator = Validator::make($request->all(), [
            'season_event_id' => 'required|integer|exists:SeasonEvent,SeasonEventID',
            'max_installments_number' => 'required|integer|min:1',
            'minimum_deposit' => 'required|integer|min:0',
            'allow_below_minimum_deposit' => 'required|in:0,1',
            'have_shirt' => 'required|in:0,1',
            'send_qr_whatsapp' => 'required|in:0,1',
            'start_date' => 'required|array|min:1',
            'start_date.*' => 'required|date',
            'end_date' => 'required|array|min:1',
            'end_date.*' => 'required|date',
            'price' => 'required|array|min:1',
            'price.*' => 'required|integer|min:0',
        ], [
            'season_event_id.required' => __('Event is required.'),
            'season_event_id.exists' => __('Selected event does not exist.'),
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
            'start_date.required' => __('At least one price interval is required.'),
            'end_date.required' => __('At least one price interval is required.'),
            'price.required' => __('At least one price interval is required.'),
            'price.*.integer' => __('Price must be a whole number without cents.'),
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
        $intervalsResult = $this->prepareAndValidateIntervals(
            $request->start_date,
            $request->end_date,
            $request->price,
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

            foreach ($intervalsResult['intervals'] as $interval) {
                DB::table('SeasonEventFinancePrice')->insert([
                    'SeasonEventID' => $seasonEventID,
                    'StartDate' => $interval['StartDate'],
                    'EndDate' => $interval['EndDate'],
                    'Price' => $interval['Price'],
                ]);
            }

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

        $intervals = DB::table('SeasonEventFinancePrice')
            ->where('SeasonEventID', $id)
            ->orderBy('StartDate')
            ->get();

        return view('finance.edit', compact('finance', 'intervals'));
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

        $validator = Validator::make($request->all(), [
            'max_installments_number' => 'required|integer|min:1',
            'minimum_deposit' => 'required|integer|min:0',
            'allow_below_minimum_deposit' => 'required|in:0,1',
            'have_shirt' => 'required|in:0,1',
            'send_qr_whatsapp' => 'required|in:0,1',
            'start_date' => 'required|array|min:1',
            'start_date.*' => 'required|date',
            'end_date' => 'required|array|min:1',
            'end_date.*' => 'required|date',
            'price' => 'required|array|min:1',
            'price.*' => 'required|integer|min:0',
        ], [
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
            'start_date.required' => __('At least one price interval is required.'),
            'end_date.required' => __('At least one price interval is required.'),
            'price.required' => __('At least one price interval is required.'),
            'price.*.integer' => __('Price must be a whole number without cents.'),
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $event = $this->getSeasonEventDetails($id);
        if (! $event) {
            return redirect()->back()->withErrors([
                'general' => __('Could not find event data.'),
            ])->withInput();
        }

        $intervalsResult = $this->prepareAndValidateIntervals(
            $request->start_date,
            $request->end_date,
            $request->price,
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

            DB::table('SeasonEventFinancePrice')
                ->where('SeasonEventID', $id)
                ->delete();

            foreach ($intervalsResult['intervals'] as $interval) {
                DB::table('SeasonEventFinancePrice')->insert([
                    'SeasonEventID' => $id,
                    'StartDate' => $interval['StartDate'],
                    'EndDate' => $interval['EndDate'],
                    'Price' => $interval['Price'],
                ]);
            }

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
            DB::table('SeasonEventFinancePrice')
                ->where('SeasonEventID', $id)
                ->delete();

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

        $prices = $request->input('price');
        if (is_array($prices)) {
            $merge['price'] = array_map(WholeNumberInput::coerce(...), $prices);
        }

        if ($merge !== []) {
            $request->merge($merge);
        }
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

    private function prepareAndValidateIntervals($startDates, $endDates, $prices, $eventStartDate)
    {
        $intervals = [];

        if (
            ! is_array($startDates) ||
            ! is_array($endDates) ||
            ! is_array($prices) ||
            count($startDates) !== count($endDates) ||
            count($endDates) !== count($prices)
        ) {
            return [
                'success' => false,
                'message' => __('Price interval data is invalid.'),
            ];
        }

        $count = count($startDates);

        for ($i = 0; $i < $count; $i++) {
            $start = trim((string) $startDates[$i]);
            $end = trim((string) $endDates[$i]);
            $price = $prices[$i];

            if ($start === '' || $end === '' || $price === '' || $price === null) {
                return [
                    'success' => false,
                    'message' => __('All price interval fields must be filled.'),
                ];
            }

            try {
                $startCarbon = Carbon::parse($start);
                $endCarbon = Carbon::parse($end);
                $eventStartCarbon = Carbon::parse($eventStartDate);
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => __('One of the interval dates is invalid.'),
                ];
            }

            if ($startCarbon->gt($endCarbon)) {
                return [
                    'success' => false,
                    'message' => __('Interval start date must be on or before the end date.'),
                ];
            }

            if ($startCarbon->gt($eventStartCarbon) || $endCarbon->gt($eventStartCarbon)) {
                return [
                    'success' => false,
                    'message' => __('No price interval may exceed the event start date.'),
                ];
            }

            $intervals[] = [
                'StartDate' => $startCarbon->format('Y-m-d'),
                'EndDate' => $endCarbon->format('Y-m-d'),
                'Price' => $price,
            ];
        }

        usort($intervals, function ($a, $b) {
            return strcmp($a['StartDate'], $b['StartDate']);
        });

        for ($i = 1; $i < count($intervals); $i++) {
            $previousEnd = Carbon::parse($intervals[$i - 1]['EndDate']);
            $expectedStart = $previousEnd->copy()->addDay()->format('Y-m-d');

            if ($intervals[$i]['StartDate'] !== $expectedStart) {
                return [
                    'success' => false,
                    'message' => __('Each interval must start the day after the previous interval ends, with no gaps or overlap.'),
                ];
            }
        }

        $lastInterval = end($intervals);
        $lastEnd = Carbon::parse($lastInterval['EndDate']);
        $eventStartCarbon = Carbon::parse($eventStartDate);

        if ($lastEnd->lt($eventStartCarbon)) {
            $autoStart = $lastEnd->copy()->addDay();

            $intervals[] = [
                'StartDate' => $autoStart->format('Y-m-d'),
                'EndDate' => $eventStartCarbon->format('Y-m-d'),
                'Price' => $lastInterval['Price'],
            ];
        }

        return [
            'success' => true,
            'intervals' => $intervals,
        ];
    }
}
