<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;
use Carbon\Carbon;

class SeasonEventFinanceController extends Controller
{
  public function index()
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

    foreach ($finance as $row) {
        $row->AllowBelowMinimumDepositText = $row->AllowBelowMinimumDeposit ? 'نعم' : 'لا';
        $row->CanEditDelete = !$this->hasPayments($row->SeasonEventID);
        $row->CanEditDeleteText = $row->CanEditDelete ? 'نعم' : 'لا، يوجد مدفوعات';
    }

    return view('finance.index', compact('finance'));
}

    public function create()
    {
        $seasons = DB::table('Season')->orderBy('SeasonYear', 'desc')->get();
        return view('finance.create', compact('seasons'));
    }

    public function getEventsForSeason(Request $request)
    {
        $seasonID = $request->query('seasonID');

        if (!$seasonID) {
            return response()->json([]);
        }

        $events = DB::table('SeasonEvent as se')
            ->join('Event as e', 'se.EventID', '=', 'e.EventID')
            ->leftJoin('SeasonEventFinance as sef', 'se.SeasonEventID', '=', 'sef.SeasonEventID')
            ->where('se.SeasonID', $seasonID)
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
    $validator = Validator::make($request->all(), [
        'season_event_id' => 'required|integer|exists:SeasonEvent,SeasonEventID',
        'max_installments_number' => 'required|integer|min:1',
        'minimum_deposit' => 'required|numeric|min:0',
        'allow_below_minimum_deposit' => 'required|in:0,1',
        'have_shirt' => 'required|in:0,1',
        'start_date' => 'required|array|min:1',
        'start_date.*' => 'required|date',
        'end_date' => 'required|array|min:1',
        'end_date.*' => 'required|date',
        'price' => 'required|array|min:1',
        'price.*' => 'required|numeric|min:0',
    ], [
        'season_event_id.required' => 'يجب اختيار الفعالية.',
        'season_event_id.exists' => 'الفعالية المختارة غير موجودة.',
        'max_installments_number.required' => 'يجب إدخال الحد الأقصى لعدد الأقساط.',
        'max_installments_number.min' => 'عدد الأقساط يجب أن يكون 1 على الأقل.',
        'minimum_deposit.required' => 'يجب إدخال الحد الأدنى للمقدم.',
        'minimum_deposit.min' => 'الحد الأدنى للمقدم لا يمكن أن يكون أقل من 0.',
        'have_shirt.required' => 'يجب تحديد هل يوجد تيشيرت أم لا.',
        'have_shirt.in' => 'قيمة التيشيرت غير صحيحة.',
        'start_date.required' => 'يجب إضافة فترة سعرية واحدة على الأقل.',
        'end_date.required' => 'يجب إضافة فترة سعرية واحدة على الأقل.',
        'price.required' => 'يجب إضافة فترة سعرية واحدة على الأقل.',
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
            'season_event_id' => 'هذه الفعالية لها إعداد مالي بالفعل.'
        ])->withInput();
    }

    $event = $this->getSeasonEventDetails($seasonEventID);
    if (!$event) {
        return redirect()->back()->withErrors([
            'season_event_id' => 'تعذر العثور على بيانات الفعالية.'
        ])->withInput();
    }

    $intervalsResult = $this->prepareAndValidateIntervals(
        $request->start_date,
        $request->end_date,
        $request->price,
        $event->EventStartDate
    );

    if (!$intervalsResult['success']) {
        return redirect()->back()->withErrors([
            'intervals' => $intervalsResult['message']
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

        return redirect()->route('finance.index')->with('success', 'تم إضافة الخطة المالية بنجاح.');
    } catch (Exception $e) {
        DB::rollBack();
        return redirect()->back()->withErrors([
            'general' => 'حدث خطأ أثناء حفظ الخطة المالية.'
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
            's.SeasonName',
            's.SeasonYear',
            'e.EventName',
            'e.EventStartDate',
            'e.EventEndDate'
        )
        ->first();

    if (!$finance) {
        abort(404);
    }

    if ($this->hasPayments($id)) {
        return redirect()->route('finance.index')->withErrors([
            'general' => 'لا يمكن تعديل هذه الخطة لوجود مدفوعات مرتبطة بها.'
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

    if (!$financeExists) {
        abort(404);
    }

    if ($this->hasPayments($id)) {
        return redirect()->route('finance.index')->withErrors([
            'general' => 'لا يمكن تعديل هذه الخطة لوجود مدفوعات مرتبطة بها.'
        ]);
    }

    $validator = Validator::make($request->all(), [
        'max_installments_number' => 'required|integer|min:1',
        'minimum_deposit' => 'required|numeric|min:0',
        'allow_below_minimum_deposit' => 'required|in:0,1',
        'have_shirt' => 'required|in:0,1',
        'start_date' => 'required|array|min:1',
        'start_date.*' => 'required|date',
        'end_date' => 'required|array|min:1',
        'end_date.*' => 'required|date',
        'price' => 'required|array|min:1',
        'price.*' => 'required|numeric|min:0',
    ], [
        'max_installments_number.required' => 'يجب إدخال الحد الأقصى لعدد الأقساط.',
        'max_installments_number.min' => 'عدد الأقساط يجب أن يكون 1 على الأقل.',
        'minimum_deposit.required' => 'يجب إدخال الحد الأدنى للمقدم.',
        'minimum_deposit.min' => 'الحد الأدنى للمقدم لا يمكن أن يكون أقل من 0.',
        'have_shirt.required' => 'يجب تحديد هل يوجد تيشيرت أم لا.',
        'have_shirt.in' => 'قيمة التيشيرت غير صحيحة.',
        'start_date.required' => 'يجب إضافة فترة سعرية واحدة على الأقل.',
        'end_date.required' => 'يجب إضافة فترة سعرية واحدة على الأقل.',
        'price.required' => 'يجب إضافة فترة سعرية واحدة على الأقل.',
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

    $event = $this->getSeasonEventDetails($id);
    if (!$event) {
        return redirect()->back()->withErrors([
            'general' => 'تعذر العثور على بيانات الفعالية.'
        ])->withInput();
    }

    $intervalsResult = $this->prepareAndValidateIntervals(
        $request->start_date,
        $request->end_date,
        $request->price,
        $event->EventStartDate
    );

    if (!$intervalsResult['success']) {
        return redirect()->back()->withErrors([
            'intervals' => $intervalsResult['message']
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

        return redirect()->route('finance.index')->with('success', 'تم تعديل الخطة المالية بنجاح.');
    } catch (Exception $e) {
        DB::rollBack();
        return redirect()->back()->withErrors([
            'general' => 'حدث خطأ أثناء تعديل الخطة المالية.'
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

        if (!$finance) {
            abort(404);
        }

        if ($this->hasPayments($id)) {
            return redirect()->route('finance.index')->withErrors([
                'general' => 'لا يمكن حذف هذه الخطة لوجود مدفوعات مرتبطة بها.'
            ]);
        }

        return view('finance.delete', compact('finance'));
    }

    public function destroy($id)
    {
        $financeExists = DB::table('SeasonEventFinance')
            ->where('SeasonEventID', $id)
            ->exists();

        if (!$financeExists) {
            abort(404);
        }

        if ($this->hasPayments($id)) {
            return redirect()->route('finance.index')->withErrors([
                'general' => 'لا يمكن حذف هذه الخطة لوجود مدفوعات مرتبطة بها.'
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

            return redirect()->route('finance.index')->with('success', 'تم حذف الخطة المالية بنجاح.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('finance.index')->withErrors([
                'general' => 'حدث خطأ أثناء حذف الخطة المالية.'
            ]);
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
            !is_array($startDates) ||
            !is_array($endDates) ||
            !is_array($prices) ||
            count($startDates) !== count($endDates) ||
            count($endDates) !== count($prices)
        ) {
            return [
                'success' => false,
                'message' => 'بيانات الفترات السعرية غير صحيحة.'
            ];
        }

        $count = count($startDates);

        for ($i = 0; $i < $count; $i++) {
            $start = trim((string)$startDates[$i]);
            $end = trim((string)$endDates[$i]);
            $price = $prices[$i];

            if ($start === '' || $end === '' || $price === '' || $price === null) {
                return [
                    'success' => false,
                    'message' => 'يجب تعبئة جميع بيانات الفترات السعرية.'
                ];
            }

            try {
                $startCarbon = Carbon::parse($start);
                $endCarbon = Carbon::parse($end);
                $eventStartCarbon = Carbon::parse($eventStartDate);
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => 'أحد تواريخ الفترات غير صحيح.'
                ];
            }

            if ($startCarbon->gt($endCarbon)) {
                return [
                    'success' => false,
                    'message' => 'تاريخ بداية الفترة يجب أن يكون قبل أو يساوي تاريخ النهاية.'
                ];
            }

            if ($startCarbon->gt($eventStartCarbon) || $endCarbon->gt($eventStartCarbon)) {
                return [
                    'success' => false,
                    'message' => 'لا يمكن أن تتجاوز أي فترة سعرية تاريخ بداية الفعالية.'
                ];
            }

            $intervals[] = [
                'StartDate' => $startCarbon->format('Y-m-d'),
                'EndDate' => $endCarbon->format('Y-m-d'),
                'Price' => $price
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
                    'message' => 'يجب أن تبدأ كل فترة من اليوم التالي مباشرة لنهاية الفترة السابقة بدون فراغات أو تداخل.'
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
                'Price' => $lastInterval['Price']
            ];
        }

        return [
            'success' => true,
            'intervals' => $intervals
        ];
    }
}