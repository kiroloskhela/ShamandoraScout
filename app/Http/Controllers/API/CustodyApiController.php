<?php

namespace App\Http\Controllers\API;

use App\Domain\Custody\CustodyRequestService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\NotificationController;
class CustodyApiController extends Controller
{
    // ---------------- helpers ----------------

    /**
 * @OA\Tag(
 *   name="Custody",
 *   description="Custody requests (inventory borrowing) endpoints"
 * )
 *
 * @OA\Get(
 *   path="/api/custody/meta",
 *   operationId="custodyMeta",
 *   tags={"Custody"},
 *   summary="Get custody metadata",
 *   description="Returns inventory items, qetaat, and event types for dropdowns.",
 *   security={{"bearerAuth":{}}},
 *   @OA\Response(
 *     response=200,
 *     description="Success",
 *     @OA\JsonContent(type="object")
 *   ),
 *   @OA\Response(
 *     response=401,
 *     description="Unauthorized",
 *     @OA\JsonContent(type="object")
 *   )
 * )
 *
 * @OA\Post(
 *   path="/api/custody/requests",
 *   operationId="custodyCreateRequest",
 *   tags={"Custody"},
 *   summary="Create custody request",
 *   description="Creates a new custody request with items. No duplicate inventory items allowed.",
 *   security={{"bearerAuth":{}}},
 *   @OA\RequestBody(
 *     required=true,
 *     @OA\JsonContent(
 *       type="object",
 *       required={"date_from","date_to","items"},
 *       @OA\Property(property="date_from", type="string", format="date", example="2026-01-21"),
 *       @OA\Property(property="date_to", type="string", format="date", example="2026-01-22"),
 *       @OA\Property(property="qetaa_id", type="integer", nullable=true, example=1),
 *       @OA\Property(property="event_type_id", type="integer", nullable=true, example=2),
 *       @OA\Property(property="user_note", type="string", nullable=true, example="Need items for event"),
 *       @OA\Property(
 *         property="items",
 *         type="array",
 *         minItems=1,
 *         @OA\Items(
 *           type="object",
 *           required={"inventory_id","qty"},
 *           @OA\Property(property="inventory_id", type="integer", example=5),
 *           @OA\Property(property="qty", type="integer", minimum=1, example=2)
 *         )
 *       )
 *     )
 *   ),
 *   @OA\Response(
 *     response=201,
 *     description="Created",
 *     @OA\JsonContent(
 *       type="object",
 *       @OA\Property(property="ok", type="boolean", example=true),
 *       @OA\Property(property="message", type="string", example="Custody request created"),
 *       @OA\Property(property="RequestID", type="integer", example=123)
 *     )
 *   ),
 *   @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(type="object")),
 *   @OA\Response(response=422, description="Validation error", @OA\JsonContent(type="object")),
 *   @OA\Response(response=500, description="Server error", @OA\JsonContent(type="object"))
 * )
 *
 * @OA\Get(
 *   path="/api/custody/requests",
 *   operationId="custodyListRequests",
 *   tags={"Custody"},
 *   summary="List my custody requests",
 *   security={{"bearerAuth":{}}},
 *   @OA\Response(
 *     response=200,
 *     description="Success",
 *     @OA\JsonContent(
 *       type="object",
 *       @OA\Property(property="ok", type="boolean", example=true),
 *       @OA\Property(property="count", type="integer", example=2),
 *       @OA\Property(property="requests", type="array", @OA\Items(type="object"))
 *     )
 *   ),
 *   @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(type="object"))
 * )
 *
 * @OA\Get(
 *   path="/api/custody/requests/{id}",
 *   operationId="custodyShowRequest",
 *   tags={"Custody"},
 *   summary="Show one custody request (must be mine)",
 *   security={{"bearerAuth":{}}},
 *   @OA\Parameter(
 *     name="id",
 *     in="path",
 *     required=true,
 *     description="RequestID",
 *     @OA\Schema(type="integer", example=123)
 *   ),
 *   @OA\Response(
 *     response=200,
 *     description="Success",
 *     @OA\JsonContent(
 *       type="object",
 *       @OA\Property(property="ok", type="boolean", example=true),
 *       @OA\Property(property="request", type="object"),
 *       @OA\Property(property="items", type="array", @OA\Items(type="object"))
 *     )
 *   ),
 *   @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(type="object")),
 *   @OA\Response(
 *     response=404,
 *     description="Not found",
 *     @OA\JsonContent(
 *       type="object",
 *       @OA\Property(property="ok", type="boolean", example=false),
 *       @OA\Property(property="message", type="string", example="Request not found")
 *     )
 *   )
 * )
 *
 * @OA\Put(
 *   path="/api/custody/requests/{id}",
 *   operationId="custodyUpdateRequest",
 *   tags={"Custody"},
 *   summary="Update pending custody request (must be mine)",
 *   description="Updates request fields and replaces items. Only allowed while status is pending.",
 *   security={{"bearerAuth":{}}},
 *   @OA\Parameter(
 *     name="id",
 *     in="path",
 *     required=true,
 *     description="RequestID",
 *     @OA\Schema(type="integer", example=123)
 *   ),
 *   @OA\RequestBody(
 *     required=true,
 *     @OA\JsonContent(
 *       type="object",
 *       required={"date_from","date_to","items"},
 *       @OA\Property(property="date_from", type="string", format="date", example="2026-01-21"),
 *       @OA\Property(property="date_to", type="string", format="date", example="2026-01-22"),
 *       @OA\Property(property="qetaa_id", type="integer", nullable=true, example=1),
 *       @OA\Property(property="event_type_id", type="integer", nullable=true, example=2),
 *       @OA\Property(property="user_note", type="string", nullable=true, example="Updated note"),
 *       @OA\Property(
 *         property="items",
 *         type="array",
 *         minItems=1,
 *         @OA\Items(
 *           type="object",
 *           required={"inventory_id","qty"},
 *           @OA\Property(property="inventory_id", type="integer", example=5),
 *           @OA\Property(property="qty", type="integer", minimum=1, example=2)
 *         )
 *       )
 *     )
 *   ),
 *   @OA\Response(
 *     response=200,
 *     description="Updated",
 *     @OA\JsonContent(
 *       type="object",
 *       @OA\Property(property="ok", type="boolean", example=true),
 *       @OA\Property(property="message", type="string", example="Request updated")
 *     )
 *   ),
 *   @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(type="object")),
 *   @OA\Response(response=403, description="Forbidden", @OA\JsonContent(type="object")),
 *   @OA\Response(response=404, description="Not found", @OA\JsonContent(type="object")),
 *   @OA\Response(response=422, description="Validation error", @OA\JsonContent(type="object")),
 *   @OA\Response(response=500, description="Server error", @OA\JsonContent(type="object"))
 * )
 *
 * @OA\Delete(
 *   path="/api/custody/requests/{id}",
 *   operationId="custodyDeleteRequest",
 *   tags={"Custody"},
 *   summary="Delete pending custody request (must be mine)",
 *   description="Only allowed while status is pending.",
 *   security={{"bearerAuth":{}}},
 *   @OA\Parameter(
 *     name="id",
 *     in="path",
 *     required=true,
 *     description="RequestID",
 *     @OA\Schema(type="integer", example=123)
 *   ),
 *   @OA\Response(
 *     response=200,
 *     description="Deleted",
 *     @OA\JsonContent(
 *       type="object",
 *       @OA\Property(property="ok", type="boolean", example=true),
 *       @OA\Property(property="message", type="string", example="Request deleted")
 *     )
 *   ),
 *   @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(type="object")),
 *   @OA\Response(response=403, description="Forbidden", @OA\JsonContent(type="object")),
 *   @OA\Response(response=404, description="Not found", @OA\JsonContent(type="object")),
 *   @OA\Response(response=500, description="Server error", @OA\JsonContent(type="object"))
 * )
 */

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
    public function store(Request $request, CustodyRequestService $custodyRequests)
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

        try {
            $requestId = $custodyRequests->create(
                $personId,
                $request->date_from,
                $request->date_to,
                $request->qetaa_id ?: null,
                $request->event_type_id ?: null,
                $request->user_note,
                $normalizedItems
            );

            NotificationController::sendToRoles(
            ['SuperAdmin','AdminInventory','Inventory'],
            'Custody Request',
            $request->user()->FirstName . ' ' . $request->user()->SecondName . ' has requested a custody on ' . $request->date_from . ' to ' . $request->date_to . '. Please review the request.'
              );

            return response()->json([
                'ok'        => true,
                'message'   => 'Custody request created',
                'RequestID' => (int)$requestId,
            ], 201);

        } catch (\Throwable $e) {
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