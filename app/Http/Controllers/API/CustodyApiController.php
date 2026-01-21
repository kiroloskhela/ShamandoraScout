<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustodyApiController extends Controller
{
    // ---------------- helpers ----------------

    private function currentPersonId(): ?int
    {
        return auth()->user()->PersonID ?? null;
    }

    private function jsonUnauthorized()
    {
        return response()->json(['ok' => false, 'message' => 'Unauthorized (missing PersonID)'], 401);
    }

    private function ensureOptionalFksExist(Request $request)
    {
        if ($request->filled('qetaa_id')) {
            $ok = DB::table('Qetaa')->where('QetaaID', (int)$request->qetaa_id)->exists();
            if (!$ok) return response()->json(['ok' => false, 'message' => 'Invalid QetaaID'], 422);
        }
        if ($request->filled('event_type_id')) {
            $ok = DB::table('EventType')->where('EventTypeID', (int)$request->event_type_id)->exists();
            if (!$ok) return response()->json(['ok' => false, 'message' => 'Invalid EventTypeID'], 422);
        }
        return null;
    }

    /**
     * Normalize + snapshot items.
     * Returns: [ok, items, errorResponse]
     */
    private function normalizeItems(array $items)
    {
        $allIds = collect($items)->pluck('inventory_id');

        // No duplicates
        if ($allIds->count() !== $allIds->unique()->count()) {
            return [false, null, response()->json(['ok' => false, 'message' => 'Duplicate inventory items are not allowed'], 422)];
        }

        $inventoryIds = $allIds->unique()->values()->map(fn($v) => (int)$v)->toArray();

        $inventoryRows = DB::table('Inventory')
            ->whereIn('InventoryID', $inventoryIds)
            ->get()
            ->keyBy('InventoryID');

        if ($inventoryRows->count() !== count($inventoryIds)) {
            return [false, null, response()->json(['ok' => false, 'message' => 'Some inventory items do not exist'], 422)];
        }

        $normalized = [];
        foreach ($items as $it) {
            $invId = (int)($it['inventory_id'] ?? 0);
            $qty   = (int)($it['qty'] ?? 0);

            if ($invId <= 0 || $qty <= 0) {
                return [false, null, response()->json(['ok' => false, 'message' => 'Invalid items payload'], 422)];
            }

            $inv = $inventoryRows[$invId];

            $normalized[] = [
                'InventoryID'      => $invId,
                'ItemNameSnapshot' => $inv->ItemName,
                'ItemUnitSnapshot' => (string)($inv->ItemMeasuringUnit ?? ''),
                'QtyRequested'     => $qty,
            ];
        }

        return [true, $normalized, null];
    }

    // ---------------- endpoints ----------------

    /**
     * GET /api/custody/meta
     * For building dropdowns in app.
     */
    public function meta()
    {
        $personId = $this->currentPersonId();
        if (!$personId) return $this->jsonUnauthorized();

        $inventory  = DB::table('Inventory')
            ->select('InventoryID', 'ItemName', 'ItemQuantity', 'ItemMeasuringUnit')
            ->orderBy('ItemName')
            ->get();

        $qetaat     = DB::table('Qetaa')->select('QetaaID', 'QetaaName')->orderBy('QetaaName')->get();
        $eventTypes = DB::table('EventType')->select('EventTypeID', 'EventTypeName')->orderBy('EventTypeName')->get();

        return response()->json([
            'ok'        => true,
            'inventory' => $inventory,
            'qetaat'    => $qetaat,
            'eventTypes'=> $eventTypes,
        ]);
    }

    /**
     * POST /api/custody/requests
     * Body JSON:
     * {
     *   "date_from":"2026-01-21",
     *   "date_to":"2026-01-22",
     *   "qetaa_id": 1,
     *   "event_type_id": 2,
     *   "user_note":"...",
     *   "items":[{"inventory_id":5,"qty":2}]
     * }
     */
    public function store(Request $request)
    {
        $personId = $this->currentPersonId();
        if (!$personId) return $this->jsonUnauthorized();

        $request->validate([
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',

            'qetaa_id'      => 'nullable|integer',
            'event_type_id' => 'nullable|integer',

            'items'                => 'required|array|min:1',
            'items.*.inventory_id' => 'required|integer',
            'items.*.qty'          => 'required|integer|min:1',

            'user_note' => 'nullable|string|max:500',
        ]);

        if ($err = $this->ensureOptionalFksExist($request)) return $err;

        [$ok, $normalizedItems, $errorResponse] = $this->normalizeItems($request->items);
        if (!$ok) return $errorResponse;

        DB::beginTransaction();
        try {
            $requestId = DB::table('CustodyRequests')->insertGetId([
                'PersonID'    => $personId,
                'QetaaID'     => $request->qetaa_id ?: null,
                'EventTypeID' => $request->event_type_id ?: null,

                'DateFrom'    => $request->date_from,
                'DateTo'      => $request->date_to,

                'Status'      => 'pending',
                'UserNote'    => $request->user_note,
                'AdminNote'   => null,
                'ReviewedBy'  => null,
                'ReviewedAt'  => null,

                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            foreach ($normalizedItems as $ni) {
                DB::table('CustodyRequestItems')->insert([
                    'RequestID'        => $requestId,
                    'InventoryID'      => $ni['InventoryID'],
                    'ItemNameSnapshot' => $ni['ItemNameSnapshot'],
                    'ItemUnitSnapshot' => $ni['ItemUnitSnapshot'],
                    'QtyRequested'     => $ni['QtyRequested'],

                    'QtyApproved'      => null,
                    'AdminItemNote'    => null,

                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'ok'        => true,
                'message'   => 'Custody request created',
                'RequestID' => (int)$requestId,
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Custody API: store failed', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Failed to create request'], 500);
        }
    }

    /**
     * GET /api/custody/requests
     * Lists my requests.
     */
    public function index()
    {
        $personId = $this->currentPersonId();
        if (!$personId) return $this->jsonUnauthorized();

        $requests = DB::table('CustodyRequests as R')
            ->leftJoin('Qetaa as Q', 'R.QetaaID', '=', 'Q.QetaaID')
            ->leftJoin('EventType as E', 'R.EventTypeID', '=', 'E.EventTypeID')
            ->leftJoin('PersonInformation as A', 'R.ReviewedBy', '=', 'A.PersonID')
            ->where('R.PersonID', $personId)
            ->orderByDesc('R.created_at')
            ->select([
                'R.RequestID',
                'R.PersonID',
                'R.QetaaID',
                'R.EventTypeID',
                'R.DateFrom',
                'R.DateTo',
                'R.Status',
                'R.UserNote',
                'R.AdminNote',
                'R.ReviewedBy',
                'R.ReviewedAt',
                'R.created_at',
                'R.updated_at',
                'Q.QetaaName',
                'E.EventTypeName',
                DB::raw("CONCAT(A.FirstName, ' ', A.SecondName) as ReviewerName"),
            ])
            ->get();

        return response()->json(['ok' => true, 'count' => $requests->count(), 'requests' => $requests]);
    }

    /**
     * GET /api/custody/requests/{id}
     * Shows one request (must be mine) + items.
     */
    public function show(int $id)
    {
        $personId = $this->currentPersonId();
        if (!$personId) return $this->jsonUnauthorized();

        $requestRow = DB::table('CustodyRequests as R')
            ->leftJoin('Qetaa as Q', 'R.QetaaID', '=', 'Q.QetaaID')
            ->leftJoin('EventType as E', 'R.EventTypeID', '=', 'E.EventTypeID')
            ->leftJoin('PersonInformation as A', 'R.ReviewedBy', '=', 'A.PersonID')
            ->where('R.RequestID', $id)
            ->where('R.PersonID', $personId)
            ->select([
                'R.*',
                'Q.QetaaName',
                'E.EventTypeName',
                DB::raw("CONCAT(A.FirstName, ' ', A.SecondName) as ReviewerName"),
            ])
            ->first();

        if (!$requestRow) {
            return response()->json(['ok' => false, 'message' => 'Request not found'], 404);
        }

        $items = DB::table('CustodyRequestItems')
            ->where('RequestID', $id)
            ->orderBy('RequestItemID')
            ->get();

        return response()->json(['ok' => true, 'request' => $requestRow, 'items' => $items]);
    }

    /**
     * PUT /api/custody/requests/{id}
     * Update pending-only (must be mine).
     */
    public function update(Request $request, int $id)
    {
        $personId = $this->currentPersonId();
        if (!$personId) return $this->jsonUnauthorized();

        $req = DB::table('CustodyRequests')
            ->where('RequestID', $id)
            ->where('PersonID', $personId)
            ->first();

        if (!$req) return response()->json(['ok' => false, 'message' => 'Request not found'], 404);
        if ($req->Status !== 'pending') {
            return response()->json(['ok' => false, 'message' => 'Cannot update after review'], 403);
        }

        $request->validate([
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',

            'qetaa_id'      => 'nullable|integer',
            'event_type_id' => 'nullable|integer',

            'user_note' => 'nullable|string|max:500',

            'items'                => 'required|array|min:1',
            'items.*.inventory_id' => 'required|integer',
            'items.*.qty'          => 'required|integer|min:1',
        ]);

        if ($err = $this->ensureOptionalFksExist($request)) return $err;

        [$ok, $normalizedItems, $errorResponse] = $this->normalizeItems($request->items);
        if (!$ok) return $errorResponse;

        DB::beginTransaction();
        try {
            DB::table('CustodyRequests')
                ->where('RequestID', $id)
                ->update([
                    'DateFrom'    => $request->date_from,
                    'DateTo'      => $request->date_to,
                    'QetaaID'     => $request->qetaa_id ?: null,
                    'EventTypeID' => $request->event_type_id ?: null,
                    'UserNote'    => $request->user_note,
                    'updated_at'  => now(),
                ]);

            DB::table('CustodyRequestItems')->where('RequestID', $id)->delete();

            $rows = [];
            foreach ($normalizedItems as $ni) {
                $rows[] = [
                    'RequestID'        => $id,
                    'InventoryID'      => $ni['InventoryID'],
                    'ItemNameSnapshot' => $ni['ItemNameSnapshot'],
                    'ItemUnitSnapshot' => $ni['ItemUnitSnapshot'],
                    'QtyRequested'     => $ni['QtyRequested'],
                    'QtyApproved'      => null,
                    'AdminItemNote'    => null,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }
            DB::table('CustodyRequestItems')->insert($rows);

            DB::commit();
            return response()->json(['ok' => true, 'message' => 'Request updated']);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Custody API: update failed', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Failed to update request'], 500);
        }
    }

    /**
     * DELETE /api/custody/requests/{id}
     * Delete pending-only (must be mine).
     */
    public function destroy(int $id)
    {
        $personId = $this->currentPersonId();
        if (!$personId) return $this->jsonUnauthorized();

        $req = DB::table('CustodyRequests')
            ->where('RequestID', $id)
            ->where('PersonID', $personId)
            ->first();

        if (!$req) return response()->json(['ok' => false, 'message' => 'Request not found'], 404);
        if ($req->Status !== 'pending') {
            return response()->json(['ok' => false, 'message' => 'Cannot delete after review'], 403);
        }

        try {
            DB::table('CustodyRequests')->where('RequestID', $id)->delete();
            return response()->json(['ok' => true, 'message' => 'Request deleted']);
        } catch (\Throwable $e) {
            Log::error('Custody API: delete failed', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Failed to delete request'], 500);
        }
    }
}