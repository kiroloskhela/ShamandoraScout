<?php

namespace App\Domain\Medicine;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Stock, lock, and dispense domain logic for medicine inventory.
 */
class MedicineInventoryService
{
    public const STOCK_LOCATION_NAME = 'ستوك';

    public const MEDICINE_TYPES = [
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

    public function decorateMedicine($medicine)
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

    public function medicineLocations(int $medicineId, bool $includeAllActive = false)
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

    public function activeLocations()
    {
        return DB::table('MedicineLocations')
            ->where('IsActive', true)
            ->orderBy('LocationName')
            ->get();
    }

    public function activeLockedAmount(int $medicineId, int $locationId): int
    {
        return (int) DB::table('MedicineStockLocks')
            ->where('MedicineID', $medicineId)
            ->where('LocationID', $locationId)
            ->whereNull('ReleasedAt')
            ->whereDate('StartsAt', '<=', today())
            ->whereDate('EndsAt', '>=', today())
            ->sum('Quantity');
    }

    public function lockedAmountForRange(int $medicineId, int $locationId, string $startsAt, string $endsAt): int
    {
        return (int) DB::table('MedicineStockLocks')
            ->where('MedicineID', $medicineId)
            ->where('LocationID', $locationId)
            ->whereNull('ReleasedAt')
            ->whereDate('StartsAt', '<=', $endsAt)
            ->whereDate('EndsAt', '>=', $startsAt)
            ->sum('Quantity');
    }

    public function stockLocationId(): int
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

    public function isStockLocation($location): bool
    {
        return trim((string) ($location->LocationName ?? '')) === self::STOCK_LOCATION_NAME;
    }

    public function syncMedicineTotal(int $medicineId): void
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

    public function updateMedicineStockTotal(int $medicineId, int $newTotal): void
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

    public function locationBreakdown($locations, string $field, string $unit): string
    {
        $parts = $locations
            ->filter(fn ($location) => (int) $location->{$field} > 0)
            ->map(fn ($location) => $location->LocationName . ': ' . $location->{$field} . ' ' . $unit)
            ->values();

        return $parts->isEmpty() ? '-' : $parts->implode(' | ');
    }

    public function stockStatus($medicine): string
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

    public function lockStatus($lock): string
    {
        if ($lock->ReleasedAt) {
            return 'تم فك الحجز';
        }

        if (Carbon::parse($lock->EndsAt)->isBefore(today())) {
            return 'انتهت المدة';
        }

        return 'محجوز';
    }

    public function typeLabel(string $type): string
    {
        return self::MEDICINE_TYPES[$type]['label'] ?? $type;
    }

    public function typeUnit(string $type): string
    {
        return self::MEDICINE_TYPES[$type]['unit'] ?? 'وحدة';
    }

    public function dispense(array $data, int $givenByPersonId): void
    {
        DB::transaction(function () use ($data, $givenByPersonId) {
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
                'GivenByPersonID' => $givenByPersonId,
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
    }

    public function redistributeStock(int $medicineId, array $amountsByLocationId): void
    {
        $medicine = DB::table('MedicineInventory')
            ->where('MedicineID', $medicineId)
            ->first();

        if (!$medicine) {
            throw ValidationException::withMessages(['amounts' => 'الدواء غير موجود.']);
        }

        $requestedTotal = collect($amountsByLocationId)->sum(fn ($amount) => (int) $amount);
        $currentTotal = (int) $medicine->Amount;

        if ($requestedTotal !== $currentTotal) {
            throw ValidationException::withMessages([
                'amounts' => "مجموع توزيع المخزون يجب أن يساوي إجمالي المخزون الحالي ({$currentTotal}).",
            ]);
        }

        DB::transaction(function () use ($medicineId, $amountsByLocationId) {
            foreach ($amountsByLocationId as $locationId => $amount) {
                $locationId = (int) $locationId;
                $amount = (int) $amount;
                $locked = $this->activeLockedAmount($medicineId, $locationId);

                if ($amount < $locked) {
                    $locationName = DB::table('MedicineLocations')
                        ->where('LocationID', $locationId)
                        ->value('LocationName');

                    throw ValidationException::withMessages([
                        'amounts.' . $locationId => "لا يمكن جعل كمية {$locationName} أقل من المحجوز حالياً ({$locked}).",
                    ]);
                }

                DB::table('MedicineStock')->updateOrInsert(
                    ['MedicineID' => $medicineId, 'LocationID' => $locationId],
                    ['Amount' => $amount, 'updated_at' => now(), 'created_at' => now()]
                );
            }

            $this->syncMedicineTotal($medicineId);
        });
    }

    /**
     * Move all stock to the stock location.
     *
     * @return string|null Error message when restock is blocked by active locks; null on success
     */
    public function restockToStockLocation(int $medicineId): ?string
    {
        $medicine = DB::table('MedicineInventory')
            ->where('MedicineID', $medicineId)
            ->first();

        if (!$medicine) {
            return 'الدواء غير موجود.';
        }

        $stockLocationId = $this->stockLocationId();

        $blockedLocations = DB::table('MedicineStockLocks as msl')
            ->join('MedicineLocations as ml', 'ml.LocationID', '=', 'msl.LocationID')
            ->where('msl.MedicineID', $medicineId)
            ->where('msl.LocationID', '!=', $stockLocationId)
            ->whereNull('msl.ReleasedAt')
            ->whereDate('msl.EndsAt', '>=', today())
            ->distinct()
            ->pluck('ml.LocationName');

        if ($blockedLocations->isNotEmpty()) {
            return 'لا يمكن عمل Restock لأن هناك حجز نشط أو مستقبلي في: ' . $blockedLocations->implode('، ') . '. فك الحجز أولاً.';
        }

        DB::transaction(function () use ($medicineId, $medicine, $stockLocationId) {
            DB::table('MedicineStock')
                ->where('MedicineID', $medicineId)
                ->where('LocationID', '!=', $stockLocationId)
                ->update([
                    'Amount' => 0,
                    'updated_at' => now(),
                ]);

            DB::table('MedicineStock')->updateOrInsert(
                ['MedicineID' => $medicineId, 'LocationID' => $stockLocationId],
                [
                    'Amount' => (int) $medicine->Amount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $this->syncMedicineTotal($medicineId);
        });

        return null;
    }
}
