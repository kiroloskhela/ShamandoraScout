<?php

namespace App\Domain\Custody;

use Illuminate\Support\Facades\DB;

class CustodyRequestService
{
    /**
     * @param  array<int, array{InventoryID: int, ItemNameSnapshot: string, ItemUnitSnapshot: string, QtyRequested: int}>  $normalizedItems
     */
    public function create(
        int $personId,
        string $dateFrom,
        string $dateTo,
        ?int $qetaaId,
        ?int $eventTypeId,
        ?string $userNote,
        array $normalizedItems
    ): int {
        return (int) DB::transaction(function () use (
            $personId,
            $dateFrom,
            $dateTo,
            $qetaaId,
            $eventTypeId,
            $userNote,
            $normalizedItems
        ) {
            $requestId = DB::table('CustodyRequests')->insertGetId([
                'PersonID' => $personId,
                'QetaaID' => $qetaaId,
                'EventTypeID' => $eventTypeId,
                'DateFrom' => $dateFrom,
                'DateTo' => $dateTo,
                'Status' => 'pending',
                'UserNote' => $userNote,
                'AdminNote' => null,
                'ReviewedBy' => null,
                'ReviewedAt' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($normalizedItems as $item) {
                DB::table('CustodyRequestItems')->insert([
                    'RequestID' => $requestId,
                    'InventoryID' => $item['InventoryID'],
                    'ItemNameSnapshot' => $item['ItemNameSnapshot'],
                    'ItemUnitSnapshot' => $item['ItemUnitSnapshot'],
                    'QtyRequested' => $item['QtyRequested'],
                    'QtyApproved' => null,
                    'AdminItemNote' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $requestId;
        });
    }
}
