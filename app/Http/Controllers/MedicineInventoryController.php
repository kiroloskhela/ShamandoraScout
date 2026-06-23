<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MedicineInventoryController extends Controller
{
    private const STOCK_LOCATION_NAME = 'ستوك';

    private const MEDICINE_TYPES = [
        'tablet' => [
            'label' => 'أقراص / حبوب',
            'unit' => 'حبة',
            'amount_label' => 'عدد الحبوب الموجودة',
        ],
        'drinkable' => [
            'label' => 'شراب',
            'unit' => 'ml',
            'amount_label' => 'الكمية الموجودة بالمللي',
        ],
        'injectable' => [
            'label' => 'حقن',
            'unit' => 'حقنة',
            'amount_label' => 'عدد الحقن الموجودة',
        ],
        'ampoule' => [
            'label' => 'أمبول',
            'unit' => 'أمبول',
            'amount_label' => 'عدد الأمبولات الموجودة',
        ],
        'ointment' => [
            'label' => 'مرهم',
            'unit' => 'جرام',
            'amount_label' => 'الكمية الموجودة بالجرام',
        ],
        'lotion' => [
            'label' => 'لوشن',
            'unit' => 'جرام',
            'amount_label' => 'الكمية الموجودة بالجرام',
        ],
        'drops' => [
            'label' => 'نقط',
            'unit' => 'نقطة',
            'amount_label' => 'عدد النقط الموجودة',
        ],
    ];

    public function index()
    {
        $medicines = DB::table('MedicineInventory')
            ->orderBy('ExpirationDate')
            ->orderBy('MedicineName')
            ->get()
            ->map(fn ($medicine) => $this->decorateMedicine($medicine));

        return view('medicine.index', compact('medicines'));
    }

    public function create()
    {
        $types = self::MEDICINE_TYPES;
        $locations = $this->activeLocations();

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

        $medicine = $this->decorateMedicine($medicine);
        $types = self::MEDICINE_TYPES;

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
            $this->updateMedicineStockTotal($id, (int) $data['amount']);

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
            ->map(fn ($medicine) => $this->decorateMedicine($medicine));

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

        DB::transaction(function () use ($data) {
            $medicine = DB::table('MedicineInventory')
                ->where('MedicineID', $data['medicine_id'])
                ->lockForUpdate()
                ->first();

            if (!$medicine) {
                throw ValidationException::withMessages(['medicine_id' => 'الدواء غير موجود.']);
            }

            if (Carbon::parse($medicine->ExpirationDate)->isBefore(today())) {
                throw ValidationException::withMessages(['medicine_id' => 'لا يمكن صرف دواء منتهي الصلاحية.']);
            }

            $stock = DB::table('MedicineStock')
                ->where('MedicineID', $data['medicine_id'])
                ->where('LocationID', $data['location_id'])
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                throw ValidationException::withMessages(['location_id' => 'هذا الدواء غير موجود في المكان المختار.']);
            }

            $locked = $this->activeLockedAmount($data['medicine_id'], $data['location_id']);
            $available = max(0, (int) $stock->Amount - $locked);

            if ($available < (int) $data['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => 'الكمية المطلوبة أكبر من المتاح في هذا المكان بعد خصم المحجوز.',
                ]);
            }

            $unit = $this->typeUnit($medicine->MedicineType);

            DB::table('MedicineDispenseRecords')->insert([
                'MedicineID' => $medicine->MedicineID,
                'LocationID' => $data['location_id'],
                'PersonID' => $data['person_id'],
                'GivenByPersonID' => Auth::user()->PersonID ?? Auth::id(),
                'Quantity' => $data['quantity'],
                'QuantityUnit' => $unit,
                'DispensedAt' => now(),
                'Notes' => $data['notes'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('MedicineStock')
                ->where('MedicineStockID', $stock->MedicineStockID)
                ->update([
                    'Amount' => (int) $stock->Amount - (int) $data['quantity'],
                    'updated_at' => now(),
                ]);

            $this->syncMedicineTotal($medicine->MedicineID);
        });

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
                $record->MedicineTypeLabel = $this->typeLabel($record->MedicineType);
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

        $medicine = $this->decorateMedicine($medicine);
        $locations = $this->medicineLocations($id, true);

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

        $requestedTotal = collect($data['amounts'])->sum(fn ($amount) => (int) $amount);
        $currentTotal = (int) $medicine->Amount;

        if ($requestedTotal !== $currentTotal) {
            throw ValidationException::withMessages([
                'amounts' => "مجموع توزيع المخزون يجب أن يساوي إجمالي المخزون الحالي ({$currentTotal}).",
            ]);
        }

        DB::transaction(function () use ($id, $data) {
            foreach ($data['amounts'] as $locationId => $amount) {
                $locationId = (int) $locationId;
                $amount = (int) $amount;
                $locked = $this->activeLockedAmount($id, $locationId);

                if ($amount < $locked) {
                    $locationName = DB::table('MedicineLocations')
                        ->where('LocationID', $locationId)
                        ->value('LocationName');

                    throw ValidationException::withMessages([
                        'amounts.' . $locationId => "لا يمكن جعل كمية {$locationName} أقل من المحجوز حالياً ({$locked}).",
                    ]);
                }

                DB::table('MedicineStock')->updateOrInsert(
                    ['MedicineID' => $id, 'LocationID' => $locationId],
                    ['Amount' => $amount, 'updated_at' => now(), 'created_at' => now()]
                );
            }

            $this->syncMedicineTotal($id);
        });

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

        $stockLocationId = $this->stockLocationId();

        $blockedLocations = DB::table('MedicineStockLocks as msl')
            ->join('MedicineLocations as ml', 'ml.LocationID', '=', 'msl.LocationID')
            ->where('msl.MedicineID', $id)
            ->where('msl.LocationID', '!=', $stockLocationId)
            ->whereNull('msl.ReleasedAt')
            ->whereDate('msl.EndsAt', '>=', today())
            ->distinct()
            ->pluck('ml.LocationName');

        if ($blockedLocations->isNotEmpty()) {
            return redirect()
                ->route('medicine.stock', $id)
                ->with('error', 'لا يمكن عمل Restock لأن هناك حجز نشط أو مستقبلي في: ' . $blockedLocations->implode('، ') . '. فك الحجز أولاً.');
        }

        DB::transaction(function () use ($id, $medicine, $stockLocationId) {
            DB::table('MedicineStock')
                ->where('MedicineID', $id)
                ->where('LocationID', '!=', $stockLocationId)
                ->update([
                    'Amount' => 0,
                    'updated_at' => now(),
                ]);

            DB::table('MedicineStock')->updateOrInsert(
                ['MedicineID' => $id, 'LocationID' => $stockLocationId],
                [
                    'Amount' => (int) $medicine->Amount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $this->syncMedicineTotal($id);
        });

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

        if ($this->isStockLocation($location)) {
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

        if ($this->isStockLocation($location)) {
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
            ->map(fn ($medicine) => $this->decorateMedicine($medicine));

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
                $lock->StatusLabel = $this->lockStatus($lock);
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

            $locked = $this->lockedAmountForRange(
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
                'QuantityUnit' => $this->typeUnit($medicine->MedicineType),
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

    public function searchPersons(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        if (mb_strlen($search) < 2) {
            return response()->json([]);
        }

        $persons = DB::table('PersonInformation as pi')
            ->leftJoin('PersonPhoneNumbers as ppn', 'ppn.PersonID', '=', 'pi.PersonID')
            ->select(
                'pi.PersonID',
                'pi.ShamandoraCode',
                DB::raw('MIN(ppn.PersonPersonalMobileNumber) as PersonPersonalMobileNumber'),
                DB::raw("CONCAT_WS(' ', pi.FirstName, pi.SecondName, pi.ThirdName, pi.FourthName) as PersonName")
            )
            ->where(function ($query) use ($search) {
                $like = "%{$search}%";
                $query->whereRaw("CONCAT_WS(' ', pi.FirstName, pi.SecondName, pi.ThirdName, pi.FourthName) LIKE ?", [$like])
                    ->orWhere('pi.PersonID', 'LIKE', $like)
                    ->orWhere('pi.ShamandoraCode', 'LIKE', $like)
                    ->orWhere('pi.RaqamQawmy', 'LIKE', $like)
                    ->orWhere('ppn.PersonPersonalMobileNumber', 'LIKE', $like);
            })
            ->groupBy('pi.PersonID', 'pi.ShamandoraCode', 'pi.FirstName', 'pi.SecondName', 'pi.ThirdName', 'pi.FourthName')
            ->orderBy('pi.ShamandoraCode')
            ->limit(20)
            ->get();

        return response()->json($persons);
    }

    private function validateMedicine(Request $request, bool $withInitialStock = false, bool $withAmount = false): array
    {
        $rules = [
            'medicine_name' => 'required|string|max:255',
            'medicine_type' => ['required', Rule::in(array_keys(self::MEDICINE_TYPES))],
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

    private function decorateMedicine($medicine)
    {
        $locations = $this->medicineLocations($medicine->MedicineID);
        $total = $locations->sum('Amount');
        $locked = $locations->sum('LockedAmount');
        $available = $locations->sum('AvailableAmount');

        $medicine->TypeLabel = $this->typeLabel($medicine->MedicineType);
        $medicine->UnitLabel = $this->typeUnit($medicine->MedicineType);
        $medicine->Amount = $total;
        $medicine->LockedAmount = $locked;
        $medicine->AvailableAmount = $available;
        $medicine->AmountText = $total . ' ' . $medicine->UnitLabel;
        $medicine->AvailableText = $available . ' ' . $medicine->UnitLabel;
        $medicine->LockedText = $locked . ' ' . $medicine->UnitLabel;
        $medicine->LocationBreakdown = $this->locationBreakdown($locations, 'Amount', $medicine->UnitLabel);
        $medicine->AvailableBreakdown = $this->locationBreakdown($locations, 'AvailableAmount', $medicine->UnitLabel);
        $medicine->LockedBreakdown = $this->locationBreakdown($locations, 'LockedAmount', $medicine->UnitLabel);
        $medicine->Locations = $locations->values();
        $medicine->StatusLabel = $this->stockStatus($medicine);
        $medicine->IsExpired = Carbon::parse($medicine->ExpirationDate)->isBefore(today());

        return $medicine;
    }

    private function medicineLocations(int $medicineId, bool $includeAllActive = false)
    {
        $lockedByLocation = DB::table('MedicineStockLocks')
            ->select('LocationID', DB::raw('SUM(Quantity) as LockedAmount'))
            ->where('MedicineID', $medicineId)
            ->whereNull('ReleasedAt')
            ->whereDate('StartsAt', '<=', today())
            ->whereDate('EndsAt', '>=', today())
            ->groupBy('LocationID')
            ->pluck('LockedAmount', 'LocationID');

        return DB::table('MedicineLocations as ml')
            ->leftJoin('MedicineStock as ms', function ($join) use ($medicineId) {
                $join->on('ms.LocationID', '=', 'ml.LocationID')
                    ->where('ms.MedicineID', '=', $medicineId);
            })
            ->where(function ($query) use ($includeAllActive) {
                if ($includeAllActive) {
                    $query->where('ml.IsActive', true)
                        ->orWhereNotNull('ms.MedicineStockID');
                    return;
                }

                $query->whereNotNull('ms.MedicineStockID');
            })
            ->select(
                'ml.LocationID',
                'ml.LocationName',
                'ml.IsActive',
                'ms.MedicineStockID',
                DB::raw('COALESCE(ms.Amount, 0) as Amount')
            )
            ->orderByDesc('ml.IsActive')
            ->orderBy('ml.LocationName')
            ->get()
            ->map(function ($location) use ($lockedByLocation) {
                $location->Amount = (int) $location->Amount;
                $location->LockedAmount = (int) ($lockedByLocation[$location->LocationID] ?? 0);
                $location->AvailableAmount = max(0, $location->Amount - $location->LockedAmount);

                return $location;
            });
    }

    private function activeLocations()
    {
        return DB::table('MedicineLocations')
            ->where('IsActive', true)
            ->orderBy('LocationName')
            ->get();
    }

    private function activeLockedAmount(int $medicineId, int $locationId): int
    {
        return (int) DB::table('MedicineStockLocks')
            ->where('MedicineID', $medicineId)
            ->where('LocationID', $locationId)
            ->whereNull('ReleasedAt')
            ->whereDate('StartsAt', '<=', today())
            ->whereDate('EndsAt', '>=', today())
            ->sum('Quantity');
    }

    private function lockedAmountForRange(int $medicineId, int $locationId, string $startsAt, string $endsAt): int
    {
        return (int) DB::table('MedicineStockLocks')
            ->where('MedicineID', $medicineId)
            ->where('LocationID', $locationId)
            ->whereNull('ReleasedAt')
            ->whereDate('StartsAt', '<=', $endsAt)
            ->whereDate('EndsAt', '>=', $startsAt)
            ->sum('Quantity');
    }

    private function stockLocationId(): int
    {
        $locationId = DB::table('MedicineLocations')
            ->where('LocationName', self::STOCK_LOCATION_NAME)
            ->value('LocationID');

        if ($locationId) {
            return (int) $locationId;
        }

        return (int) DB::table('MedicineLocations')->insertGetId([
            'LocationName' => self::STOCK_LOCATION_NAME,
            'IsActive' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function isStockLocation($location): bool
    {
        return trim((string) ($location->LocationName ?? '')) === self::STOCK_LOCATION_NAME;
    }

    private function syncMedicineTotal(int $medicineId): void
    {
        $total = (int) DB::table('MedicineStock')
            ->where('MedicineID', $medicineId)
            ->sum('Amount');

        DB::table('MedicineInventory')
            ->where('MedicineID', $medicineId)
            ->update([
                'Amount' => $total,
                'updated_at' => now(),
            ]);
    }

    private function updateMedicineStockTotal(int $medicineId, int $newTotal): void
    {
        $stocks = DB::table('MedicineStock')
            ->where('MedicineID', $medicineId)
            ->lockForUpdate()
            ->get();

        $currentTotal = (int) $stocks->sum('Amount');
        $difference = $newTotal - $currentTotal;

        if ($difference === 0) {
            return;
        }

        $stockLocationId = $this->stockLocationId();
        $stockRow = $stocks->firstWhere('LocationID', $stockLocationId);
        $stockAmount = (int) ($stockRow->Amount ?? 0);

        if ($difference < 0) {
            $requestedDecrease = abs($difference);
            $locked = $this->activeLockedAmount($medicineId, $stockLocationId);
            $availableInStockLocation = max(0, $stockAmount - $locked);

            if ($availableInStockLocation < $requestedDecrease) {
                throw ValidationException::withMessages([
                    'amount' => 'لا يمكن تقليل إجمالي الكمية بهذا المقدار لأن الكمية المتاحة في ستوك غير كافية. عدّل توزيع المخزون أولاً.',
                ]);
            }
        }

        $newStockAmount = $stockAmount + $difference;

        if ($stockRow) {
            DB::table('MedicineStock')
                ->where('MedicineStockID', $stockRow->MedicineStockID)
                ->update([
                    'Amount' => $newStockAmount,
                    'updated_at' => now(),
                ]);

            return;
        }

        DB::table('MedicineStock')->insert([
            'MedicineID' => $medicineId,
            'LocationID' => $stockLocationId,
            'Amount' => $newStockAmount,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function locationBreakdown($locations, string $field, string $unit): string
    {
        $parts = $locations
            ->filter(fn ($location) => (int) $location->{$field} > 0)
            ->map(fn ($location) => $location->LocationName . ': ' . $location->{$field} . ' ' . $unit)
            ->values();

        return $parts->isEmpty() ? '-' : $parts->implode(' | ');
    }

    private function stockStatus($medicine): string
    {
        $expiration = Carbon::parse($medicine->ExpirationDate);

        if ($expiration->isBefore(today())) {
            return 'منتهي الصلاحية';
        }

        if ((int) $medicine->AvailableAmount === 0 && (int) $medicine->LockedAmount > 0) {
            return 'محجوز بالكامل';
        }

        if ((int) $medicine->AvailableAmount === 0) {
            return 'نفدت الكمية';
        }

        if ((int) $medicine->LockedAmount > 0) {
            return 'متاح مع حجز';
        }

        if ($expiration->diffInDays(today()) <= 30) {
            return 'قريب الانتهاء';
        }

        return 'متاح';
    }

    private function lockStatus($lock): string
    {
        if ($lock->ReleasedAt) {
            return 'تم فك الحجز';
        }

        if (Carbon::parse($lock->EndsAt)->isBefore(today())) {
            return 'انتهت المدة';
        }

        return 'محجوز';
    }

    private function typeLabel(string $type): string
    {
        return self::MEDICINE_TYPES[$type]['label'] ?? $type;
    }

    private function typeUnit(string $type): string
    {
        return self::MEDICINE_TYPES[$type]['unit'] ?? 'وحدة';
    }
}
