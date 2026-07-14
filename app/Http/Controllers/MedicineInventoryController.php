<?php

namespace App\Http\Controllers;

use App\Domain\Medicine\MedicineInventoryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MedicineInventoryController extends Controller
{
    public function __construct(
        private MedicineInventoryService $medicineInventory
    ) {
    }

    public function index()
    {
        $medicines = DB::table('MedicineInventory')
            ->orderBy('ExpirationDate')
            ->orderBy('MedicineName')
            ->get()
            ->map(fn ($medicine) => $this->medicineInventory->decorateMedicine($medicine));

        return view('medicine.index', compact('medicines'));
    }

    public function create()
    {
        $types = MedicineInventoryService::MEDICINE_TYPES;
        $locations = $this->medicineInventory->activeLocations();

        return view('medicine.create', compact('types', 'locations'));
    }

    public function insert(Request $request)
    {
        $data = $this->validateMedicine($request, true);

        DB::transaction(function () use ($data) {
            $medicineId = DB::table('MedicineInventory')->insertGetId([
                'MedicineName' => $data['medicine_name'],
                'MedicineType' => $data['medicine_type'],
                'ExpirationDate' => $data['expiration_date'],
                'Amount' => $data['amount'],
                'Notes' => $data['notes'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('MedicineStock')->insert([
                'MedicineID' => $medicineId,
                'LocationID' => $data['location_id'],
                'Amount' => $data['amount'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()
            ->route('medicine.index')
            ->with('status', 'تمت إضافة الدواء بنجاح.');
    }

    public function edit($id)
    {
        $medicine = DB::table('MedicineInventory')
            ->where('MedicineID', $id)
            ->first();

        if (!$medicine) {
            return redirect()
                ->route('medicine.index')
                ->with('error', 'الدواء غير موجود.');
        }

        $medicine = $this->medicineInventory->decorateMedicine($medicine);
        $types = MedicineInventoryService::MEDICINE_TYPES;

        return view('medicine.edit', compact('medicine', 'types'));
    }

    public function update(Request $request, $id)
    {
        $medicine = DB::table('MedicineInventory')
            ->where('MedicineID', $id)
            ->first();

        if (!$medicine) {
            return redirect()
                ->route('medicine.index')
                ->with('error', 'الدواء غير موجود.');
        }

        $data = $this->validateMedicine($request, false, true);

        DB::transaction(function () use ($id, $data) {
            $this->medicineInventory->updateMedicineStockTotal($id, (int) $data['amount']);

            DB::table('MedicineInventory')
                ->where('MedicineID', $id)
                ->update([
                    'MedicineName' => $data['medicine_name'],
                    'MedicineType' => $data['medicine_type'],
                    'ExpirationDate' => $data['expiration_date'],
                    'Amount' => $data['amount'],
                    'Notes' => $data['notes'] ?? null,
                    'updated_at' => now(),
                ]);
        });

        return redirect()
            ->route('medicine.index')
            ->with('status', 'تم تحديث بيانات الدواء بنجاح.');
    }

    public function delete($id)
    {
        $medicine = DB::table('MedicineInventory')
            ->where('MedicineID', $id)
            ->first();

        if (!$medicine) {
            return redirect()
                ->route('medicine.index')
                ->with('error', 'الدواء غير موجود.');
        }

        $dispenseCount = DB::table('MedicineDispenseRecords')
            ->where('MedicineID', $id)
            ->count();
        $lockCount = DB::table('MedicineStockLocks')
            ->where('MedicineID', $id)
            ->count();

        return view('medicine.delete', compact('medicine', 'dispenseCount', 'lockCount'));
    }

    public function destroy($id)
    {
        $dispenseCount = DB::table('MedicineDispenseRecords')
            ->where('MedicineID', $id)
            ->count();
        $lockCount = DB::table('MedicineStockLocks')
            ->where('MedicineID', $id)
            ->count();

        if ($dispenseCount > 0 || $lockCount > 0) {
            return redirect()
                ->route('medicine.index')
                ->with('error', 'لا يمكن حذف دواء له سجل صرف أو حجز. يمكن جعل الكمية صفر بدل الحذف.');
        }

        DB::table('MedicineInventory')
            ->where('MedicineID', $id)
            ->delete();

        return redirect()
            ->route('medicine.index')
            ->with('status', 'تم حذف الدواء بنجاح.');
    }

    public function dispense()
    {
        $medicines = DB::table('MedicineInventory')
            ->orderBy('MedicineName')
            ->get()
            ->map(fn ($medicine) => $this->medicineInventory->decorateMedicine($medicine));

        return view('medicine.dispense', compact('medicines'));
    }

    public function storeDispense(Request $request)
    {
        $data = $request->validate([
            'medicine_id' => 'required|integer|exists:MedicineInventory,MedicineID',
            'location_id' => 'required|integer|exists:MedicineLocations,LocationID',
            'person_id' => 'required|integer|exists:PersonInformation,PersonID',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:2000',
        ], [], [
            'medicine_id' => 'الدواء',
            'location_id' => 'مكان الدواء',
            'person_id' => 'الشخص',
            'quantity' => 'الكمية',
            'notes' => 'ملاحظات',
        ]);

        $givenByPersonId = (int) (Auth::user()->PersonID ?? Auth::id());
        $this->medicineInventory->dispense($data, $givenByPersonId);

        return redirect()
            ->route('medicine.records')
            ->with('status', 'تم تسجيل صرف الدواء وخصم الكمية من المكان المختار.');
    }

    public function records()
    {
        $records = DB::table('MedicineDispenseRecords as mdr')
            ->join('MedicineInventory as mi', 'mi.MedicineID', '=', 'mdr.MedicineID')
            ->leftJoin('MedicineLocations as ml', 'ml.LocationID', '=', 'mdr.LocationID')
            ->leftJoin('PersonInformation as p', 'p.PersonID', '=', 'mdr.PersonID')
            ->leftJoin('PersonInformation as giver', 'giver.PersonID', '=', 'mdr.GivenByPersonID')
            ->select(
                'mdr.MedicineDispenseID',
                'mi.MedicineName',
                'mi.MedicineType',
                'ml.LocationName',
                'mdr.Quantity',
                'mdr.QuantityUnit',
                'mdr.DispensedAt',
                'mdr.Notes',
                DB::raw("CONCAT_WS(' ', p.FirstName, p.SecondName, p.ThirdName, p.FourthName) as PersonName"),
                DB::raw("CONCAT_WS(' ', giver.FirstName, giver.SecondName, giver.ThirdName, giver.FourthName) as GiverName")
            )
            ->orderByDesc('mdr.DispensedAt')
            ->get()
            ->map(function ($record) {
                $record->MedicineTypeLabel = $this->medicineInventory->typeLabel($record->MedicineType);
                $record->QuantityText = $record->Quantity . ' ' . $record->QuantityUnit;
                $record->DispensedAtText = Carbon::parse($record->DispensedAt)->format('Y-m-d H:i');
                $record->LocationName = $record->LocationName ?: '-';
                $record->Notes = $record->Notes ?: '-';
                $record->GiverName = trim($record->GiverName) ?: '-';
                $record->PersonName = trim($record->PersonName) ?: '-';

                return $record;
            });

        return view('medicine.records', compact('records'));
    }

    public function stock($id)
    {
        $medicine = DB::table('MedicineInventory')
            ->where('MedicineID', $id)
            ->first();

        if (!$medicine) {
            return redirect()->route('medicine.index')->with('error', 'الدواء غير موجود.');
        }

        $medicine = $this->medicineInventory->decorateMedicine($medicine);
        $locations = $this->medicineInventory->medicineLocations($id, true);

        return view('medicine.stock', compact('medicine', 'locations'));
    }

    public function updateStock(Request $request, $id)
    {
        $medicine = DB::table('MedicineInventory')
            ->where('MedicineID', $id)
            ->first();

        if (!$medicine) {
            return redirect()->route('medicine.index')->with('error', 'الدواء غير موجود.');
        }

        $data = $request->validate([
            'amounts' => 'required|array',
            'amounts.*' => 'required|integer|min:0',
        ]);

        $this->medicineInventory->redistributeStock((int) $id, $data['amounts']);

        return redirect()
            ->route('medicine.stock', $id)
            ->with('status', 'تم تحديث توزيع المخزون بنجاح.');
    }

    public function restock($id)
    {
        $medicine = DB::table('MedicineInventory')
            ->where('MedicineID', $id)
            ->first();

        if (!$medicine) {
            return redirect()->route('medicine.index')->with('error', 'الدواء غير موجود.');
        }

        $error = $this->medicineInventory->restockToStockLocation((int) $id);

        if ($error !== null) {
            return redirect()
                ->route('medicine.stock', $id)
                ->with('error', $error);
        }

        return redirect()
            ->route('medicine.stock', $id)
            ->with('status', 'تم عمل Restock ونقل كل الكمية إلى ستوك.');
    }

    public function locations()
    {
        $locations = DB::table('MedicineLocations')
            ->orderByDesc('IsActive')
            ->orderBy('LocationName')
            ->get();

        return view('medicine.locations', compact('locations'));
    }

    public function storeLocation(Request $request)
    {
        $data = $request->validate([
            'location_name' => 'required|string|max:255|unique:MedicineLocations,LocationName',
        ], [], [
            'location_name' => 'اسم المكان',
        ]);

        DB::table('MedicineLocations')->insert([
            'LocationName' => $data['location_name'],
            'IsActive' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('medicine.locations')->with('status', 'تمت إضافة المكان بنجاح.');
    }

    public function updateLocation(Request $request, $id)
    {
        $location = DB::table('MedicineLocations')
            ->where('LocationID', $id)
            ->first();

        if (!$location) {
            return redirect()->route('medicine.locations')->with('error', 'المكان غير موجود.');
        }

        if ($this->medicineInventory->isStockLocation($location)) {
            return redirect()->route('medicine.locations')->with('error', 'مكان ستوك ثابت ولا يمكن تعديله.');
        }

        $data = $request->validate([
            'location_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('MedicineLocations', 'LocationName')->ignore($id, 'LocationID'),
            ],
            'is_active' => 'nullable|boolean',
        ], [], [
            'location_name' => 'اسم المكان',
        ]);

        DB::table('MedicineLocations')
            ->where('LocationID', $id)
            ->update([
                'LocationName' => $data['location_name'],
                'IsActive' => $request->boolean('is_active'),
                'updated_at' => now(),
            ]);

        return redirect()->route('medicine.locations')->with('status', 'تم تحديث المكان بنجاح.');
    }

    public function destroyLocation($id)
    {
        $location = DB::table('MedicineLocations')
            ->where('LocationID', $id)
            ->first();

        if (!$location) {
            return redirect()->route('medicine.locations')->with('error', 'المكان غير موجود.');
        }

        if ($this->medicineInventory->isStockLocation($location)) {
            return redirect()->route('medicine.locations')->with('error', 'مكان ستوك ثابت ولا يمكن حذفه.');
        }

        $stockAmount = (int) DB::table('MedicineStock')
            ->where('LocationID', $id)
            ->sum('Amount');

        if ($stockAmount > 0) {
            return redirect()
                ->route('medicine.locations')
                ->with('error', "لا يمكن حذف {$location->LocationName}. من فضلك فضي كل الأدوية من هذا المكان قبل الحذف.");
        }

        $lockCount = DB::table('MedicineStockLocks')
            ->where('LocationID', $id)
            ->count();

        if ($lockCount > 0) {
            return redirect()
                ->route('medicine.locations')
                ->with('error', "لا يمكن حذف {$location->LocationName} لأن له سجلات حجز.");
        }

        DB::transaction(function () use ($id) {
            DB::table('MedicineStock')
                ->where('LocationID', $id)
                ->where('Amount', 0)
                ->delete();

            DB::table('MedicineLocations')
                ->where('LocationID', $id)
                ->delete();
        });

        return redirect()->route('medicine.locations')->with('status', 'تم حذف المكان بنجاح.');
    }

    public function locks()
    {
        $medicines = DB::table('MedicineInventory')
            ->orderBy('MedicineName')
            ->get()
            ->map(fn ($medicine) => $this->medicineInventory->decorateMedicine($medicine));

        $locks = DB::table('MedicineStockLocks as msl')
            ->join('MedicineInventory as mi', 'mi.MedicineID', '=', 'msl.MedicineID')
            ->join('MedicineLocations as ml', 'ml.LocationID', '=', 'msl.LocationID')
            ->leftJoin('PersonInformation as creator', 'creator.PersonID', '=', 'msl.CreatedByPersonID')
            ->select(
                'msl.MedicineStockLockID',
                'mi.MedicineName',
                'ml.LocationName',
                'msl.Quantity',
                'msl.QuantityUnit',
                'msl.LockReason',
                'msl.StartsAt',
                'msl.EndsAt',
                'msl.ReleasedAt',
                'msl.Notes',
                DB::raw("CONCAT_WS(' ', creator.FirstName, creator.SecondName, creator.ThirdName, creator.FourthName) as CreatorName")
            )
            ->orderByRaw('CASE WHEN msl.ReleasedAt IS NULL AND msl.EndsAt >= CURDATE() THEN 0 ELSE 1 END')
            ->orderByDesc('msl.StartsAt')
            ->get()
            ->map(function ($lock) {
                $lock->QuantityText = $lock->Quantity . ' ' . $lock->QuantityUnit;
                $lock->StatusLabel = $this->medicineInventory->lockStatus($lock);
                $lock->LockReason = $lock->LockReason ?: '-';
                $lock->Notes = $lock->Notes ?: '-';
                $lock->CreatorName = trim($lock->CreatorName) ?: '-';

                return $lock;
            });

        return view('medicine.locks', compact('medicines', 'locks'));
    }

    public function storeLock(Request $request)
    {
        $data = $request->validate([
            'medicine_id' => 'required|integer|exists:MedicineInventory,MedicineID',
            'location_id' => 'required|integer|exists:MedicineLocations,LocationID',
            'quantity' => 'required|integer|min:1',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after_or_equal:starts_at',
            'lock_reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ], [], [
            'medicine_id' => 'الدواء',
            'location_id' => 'المكان',
            'quantity' => 'الكمية',
            'starts_at' => 'تاريخ البداية',
            'ends_at' => 'تاريخ النهاية',
        ]);

        DB::transaction(function () use ($data) {
            $medicine = DB::table('MedicineInventory')
                ->where('MedicineID', $data['medicine_id'])
                ->lockForUpdate()
                ->first();

            if (!$medicine) {
                throw ValidationException::withMessages(['medicine_id' => 'الدواء غير موجود.']);
            }

            if (Carbon::parse($medicine->ExpirationDate)->isBefore(today())) {
                throw ValidationException::withMessages(['medicine_id' => 'لا يمكن حجز دواء منتهي الصلاحية.']);
            }

            $stock = DB::table('MedicineStock')
                ->where('MedicineID', $data['medicine_id'])
                ->where('LocationID', $data['location_id'])
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                throw ValidationException::withMessages(['location_id' => 'هذا الدواء غير موجود في المكان المختار.']);
            }

            $locked = $this->medicineInventory->lockedAmountForRange(
                $data['medicine_id'],
                $data['location_id'],
                $data['starts_at'],
                $data['ends_at']
            );
            $available = max(0, (int) $stock->Amount - $locked);

            if ($available < (int) $data['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => 'الكمية المطلوبة أكبر من المتاح في هذا المكان بعد خصم المحجوز.',
                ]);
            }

            DB::table('MedicineStockLocks')->insert([
                'MedicineID' => $data['medicine_id'],
                'LocationID' => $data['location_id'],
                'CreatedByPersonID' => Auth::user()->PersonID ?? Auth::id(),
                'Quantity' => $data['quantity'],
                'QuantityUnit' => $this->medicineInventory->typeUnit($medicine->MedicineType),
                'LockReason' => $data['lock_reason'] ?? null,
                'StartsAt' => $data['starts_at'],
                'EndsAt' => $data['ends_at'],
                'Notes' => $data['notes'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('medicine.locks')->with('status', 'تم حجز كمية الدواء بنجاح.');
    }

    public function releaseLock($id)
    {
        DB::table('MedicineStockLocks')
            ->where('MedicineStockLockID', $id)
            ->whereNull('ReleasedAt')
            ->update([
                'ReleasedAt' => now(),
                'updated_at' => now(),
            ]);

        return redirect()->route('medicine.locks')->with('status', 'تم فك الحجز بنجاح.');
    }

    public function searchPersons(Request $request, \App\Domain\Person\PersonSearchService $personSearch)
    {
        $term = \App\Support\LikeSearch::fromRequest($request, ['search', 'q'], 2);

        return response()->json($personSearch->typeaheadWithPhone($term));
    }

    private function validateMedicine(Request $request, bool $withInitialStock = false, bool $withAmount = false): array
    {
        $rules = [
            'medicine_name' => 'required|string|max:255',
            'medicine_type' => ['required', Rule::in(array_keys(MedicineInventoryService::MEDICINE_TYPES))],
            'expiration_date' => 'required|date',
            'notes' => 'nullable|string|max:2000',
        ];

        if ($withInitialStock || $withAmount) {
            $rules['amount'] = 'required|integer|min:0';
        }

        if ($withInitialStock) {
            $rules['location_id'] = 'required|integer|exists:MedicineLocations,LocationID';
        }

        return $request->validate($rules, [], [
            'medicine_name' => 'اسم الدواء',
            'medicine_type' => 'نوع الدواء',
            'expiration_date' => 'تاريخ انتهاء الصلاحية',
            'amount' => 'الكمية',
            'location_id' => 'مكان الدواء',
            'notes' => 'ملاحظات',
        ]);
    }
}
