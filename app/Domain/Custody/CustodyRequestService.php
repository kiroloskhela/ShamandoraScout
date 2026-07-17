<?php

namespace App\Domain\Custody;

use Illuminate\Support\Facades\DB;
use RuntimeException;

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

            $this->replaceItems($requestId, $normalizedItems);

            return $requestId;
        });
    }

    /**
     * @param  array<int, array{InventoryID: int, ItemNameSnapshot: string, ItemUnitSnapshot: string, QtyRequested: int}>  $normalizedItems
     */
    public function updatePending(
        int $requestId,
        int $personId,
        string $dateFrom,
        string $dateTo,
        ?int $qetaaId,
        ?int $eventTypeId,
        ?string $userNote,
        array $normalizedItems
    ): void {
        DB::transaction(function () use (
            $requestId,
            $personId,
            $dateFrom,
            $dateTo,
            $qetaaId,
            $eventTypeId,
            $userNote,
            $normalizedItems
        ) {
            $updated = DB::table('CustodyRequests')
                ->where('RequestID', $requestId)
                ->where('PersonID', $personId)
                ->where('Status', 'pending')
                ->update([
                    'DateFrom' => $dateFrom,
                    'DateTo' => $dateTo,
                    'QetaaID' => $qetaaId,
                    'EventTypeID' => $eventTypeId,
                    'UserNote' => $userNote,
                    'updated_at' => now(),
                ]);

            if ($updated === 0) {
                throw new RuntimeException('Custody request not pending or not owned');
            }

            DB::table('CustodyRequestItems')->where('RequestID', $requestId)->delete();
            $this->replaceItems($requestId, $normalizedItems);
        });
    }

    public function deletePending(int $requestId, int $personId): void
    {
        $deleted = DB::table('CustodyRequests')
            ->where('RequestID', $requestId)
            ->where('PersonID', $personId)
            ->where('Status', 'pending')
            ->delete();

        if ($deleted === 0) {
            throw new RuntimeException('Custody request not pending or not owned');
        }
    }

    /**
     * @param  array<int, array{InventoryID: int, ItemNameSnapshot: string, ItemUnitSnapshot: string, QtyRequested: int}>  $normalizedItems
     */
    private function replaceItems(int $requestId, array $normalizedItems): void
    {
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
    }
}
