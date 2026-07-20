<?php

namespace App\Http\Controllers;

use App\Domain\Custody\CustodyRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustodyRequestController extends Controller
{
    // ===== Helpers =====
    private function currentPersonId()
    {
        return auth()->user()->PersonID ?? null;
    }

    // صفحة إنشاء طلب
    public function create()
    {
        $this->authorize('custody.create');

        $inventory = DB::table('Inventory')->orderBy('ItemName')->get();
        $qetaat = DB::table('Qetaa')->orderBy('QetaaName')->get();
        $eventTypes = DB::table('EventType')->orderBy('EventTypeName')->get();

        return view('custody_requests.create', compact('inventory', 'qetaat', 'eventTypes'));
    }

    // حفظ الطلب
    public function store(Request $request, CustodyRequestService $custodyRequests)
    {
        $this->authorize('custody.create');

        $personId = $this->currentPersonId();
        if (! $personId) {
            return back()->with('error', __('Cannot determine current user (PersonID).'))->withInput();
        }

        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',

            'qetaa_id' => 'nullable|integer',
            'event_type_id' => 'nullable|integer',

            'items' => 'required|array|min:1',
            'items.*.inventory_id' => 'required|integer',
            'items.*.qty' => 'required|integer|min:1',

            'user_note' => 'nullable|string|max:500',
        ], [
            'items.required' => __('Please select at least one item.'),
            'items.min' => __('Please select at least one item.'),
        ]);

        // validate optional foreign keys exist
        if ($request->qetaa_id && ! DB::table('Qetaa')->where('QetaaID', $request->qetaa_id)->exists()) {
            return back()->with('error', __('Invalid sector.'))->withInput();
        }
        if ($request->event_type_id && ! DB::table('EventType')->where('EventTypeID', $request->event_type_id)->exists()) {
            return back()->with('error', __('Invalid event type.'))->withInput();
        }

        // Load inventory snapshots
        $allInventoryIds = collect($request->items)->pluck('inventory_id');
        if ($allInventoryIds->count() !== $allInventoryIds->unique()->count()) {
            return back()->with('error', __('Duplicate items found.'))->withInput();
        }

        $inventoryIds = $allInventoryIds->unique()->values();
        $inventoryRows = DB::table('Inventory')
            ->whereIn('InventoryID', $inventoryIds)
            ->get()
            ->keyBy('InventoryID');

        $normalizedItems = [];
        foreach ($request->items as $it) {
            $invId = (int) $it['inventory_id'];
            $qty = (int) $it['qty'];

            if (! isset($inventoryRows[$invId])) {
                return back()->with('error', __('An item does not exist in inventory.'))->withInput();
            }

            $inv = $inventoryRows[$invId];

            $normalizedItems[] = [
                'InventoryID' => $invId,
                'ItemNameSnapshot' => $inv->ItemName,
                'ItemUnitSnapshot' => (string) ($inv->ItemMeasuringUnit ?? ''),
                'QtyRequested' => $qty,
            ];
        }

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
                ['SuperAdmin', 'AdminInventory', 'Inventory'],
                'Custody Request',
                $request->user()->FirstName.' '.$request->user()->SecondName.' has requested a custody on '.$request->date_from.' to '.$request->date_to.'. Please review the request.'
            );
            // Attempt to notify a person with RoleName 'AdminInventory' via WhatsApp (non-blocking)
            // Attempt to notify a person with RoleName 'AdminInventory' via WhatsApp (non-blocking)
            // Attempt to notify a person with RoleName 'AdminInventory' via WhatsApp (non-blocking)
            // try {
            //     Log::info('CustodyRequest: finding AdminInventory role');

            //     $admin = DB::table('PersonRole as pr')
            //         ->join('Roles as r', 'pr.RoleID', '=', 'r.RoleID')
            //         ->join('PersonPhoneNumbers as pp', 'pp.PersonID', '=', 'pr.PersonID')
            //         ->where('r.RoleName', 'AdminInventory')
            //         ->whereNotNull('pp.PersonPersonalMobileNumber')
            //         ->select('pr.PersonID', 'pp.PersonPersonalMobileNumber')
            //         ->orderBy('pr.PersonRoleID', 'asc')
            //         ->first();

            //     if (!$admin) {
            //         Log::warning("CustodyRequest: no admin found to notify");
            //     } else {
            //         Log::info("CustodyRequest: admin found", ['admin' => $admin]);

            //         $user = auth()->user();
            //         $fullName = trim(implode(' ', array_filter([
            //             $user->FirstName ?? null,
            //             $user->SecondName ?? null,
            //             $user->ThirdName ?? null,
            //         ])));
            //         $code = $user->ShamandoraCode ?? '';

            //         Log::info('CustodyRequest: building items text');

            //         $itemsText = "";
            //         foreach ($normalizedItems as $ni) {
            //             $unit = $ni['ItemUnitSnapshot'] !== '' ? " ({$ni['ItemUnitSnapshot']})" : '';
            //             $itemsText .= "- {$ni['ItemNameSnapshot']}{$unit} x {$ni['QtyRequested']}\n";
            //         }

            //         $link = route('admin.custody_requests.show', $requestId);

            //         $message = "هناك طلب عهدة جديد (#{$requestId})\n"
            //                  . "المستخدم: {$fullName} {$code}\n"
            //                  . "التواريخ: من {$request->date_from} إلى {$request->date_to}\n"
            //                  . "الأصناف:\n{$itemsText}\n"
            //                  . "مراجعة: {$link}";

            //         Log::info('CustodyRequest: message built', ['message' => $message]);

            //         // --- Normalize number here if you want to force +20 ---
            //         $rawNumber = $admin->PersonPersonalMobileNumber;
            //         $normalizedNumber = '+2' . ltrim(preg_replace('/\D+/', '', $rawNumber), '0'); // logs can check format

            //         Log::info('CustodyRequest: normalized phone number', [
            //             'raw' => $rawNumber,
            //             'normalized' => $normalizedNumber,
            //         ]);
            //         $payload = [
            //             'full_number' => $normalizedNumber,
            //             'message'     => $message,
            //         ];

            //         Log::info('CustodyRequest: sending WhatsApp', ['payload' => $payload]);

            //         $fake = \Illuminate\Http\Request::create('/whatsapp/send-with-header', 'POST', $payload);

            //         $waController = app(\App\Http\Controllers\WhatsAppBridgeController::class);
            //         $response = $waController->sendWithHeader($fake);

            //         Log::info('CustodyRequest: WhatsApp response', ['response' => $response]);
            //     }
            // } catch (\Throwable $e) {
            //     \Illuminate\Support\Facades\Log::error('Failed sending WhatsApp notification for custody request', [
            //         'requestId' => $requestId,
            //         'error'     => $e->getMessage(),
            //     ]);
            // }

            return redirect()->route('custody_requests.my')
                ->with('success', __('Custody request submitted successfully and is now under review.'));
        } catch (\Throwable $e) {
            return back()->with('error', __('An error occurred while saving the request.'))->withInput();
        }
    }

    // قائمة طلباتي (مع بيانات إضافية + اسم المُراجع)
    public function my()
    {
        $personId = $this->currentPersonId();
        if (! $personId) {
            return redirect()->back()->with('error', __('Cannot determine current user (PersonID).'));
        }

        $requests = DB::table('CustodyRequests as R')
            ->leftJoin('Qetaa as Q', 'R.QetaaID', '=', 'Q.QetaaID')
            ->leftJoin('EventType as E', 'R.EventTypeID', '=', 'E.EventTypeID')
            ->leftJoin('PersonInformation as A', 'R.ReviewedBy', '=', 'A.PersonID')
            ->where('R.PersonID', $personId)
            ->orderByDesc('R.created_at')
            ->select([
                'R.*',
                'Q.QetaaName',
                'E.EventTypeName',
                DB::raw("CONCAT(A.FirstName, ' ', A.SecondName) as ReviewerName"),
            ])
            ->get();

        return view('custody_requests.my', compact('requests'));
    }

    // عرض تفاصيل طلب واحد (مع sector/eventType/reviewer)
    public function show($id)
    {
        $this->authorize('custody.view', (int) $id);

        $personId = $this->currentPersonId();
        if (! $personId) {
            return redirect()->back()->with('error', __('Cannot determine current user (PersonID).'));
        }

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

        if (! $requestRow) {
            return redirect()->route('custody_requests.my')->with('error', __('Request not found.'));
        }

        $items = DB::table('CustodyRequestItems')
            ->where('RequestID', $id)
            ->orderBy('RequestItemID')
            ->get();

        return view('custody_requests.show', compact('requestRow', 'items'));
    }

    public function edit($id)
    {
        $this->authorize('custody.update', (int) $id);

        $personId = auth()->user()->PersonID ?? null;
        if (! $personId) {
            return redirect()->route('custody_requests.my')->with('error', __('Cannot determine current user.'));
        }

        // Request (must be owned + pending)
        $requestRow = DB::table('CustodyRequests as R')
            ->leftJoin('Qetaa as Q', 'R.QetaaID', '=', 'Q.QetaaID')
            ->leftJoin('EventType as E', 'R.EventTypeID', '=', 'E.EventTypeID')
            ->select([
                'R.*',
                'Q.QetaaName',
                'E.EventTypeName',
            ])
            ->where('R.RequestID', $id)
            ->where('R.PersonID', $personId)
            ->first();

        if (! $requestRow) {
            return redirect()->route('custody_requests.my')->with('error', __('Request not found.'));
        }

        if ($requestRow->Status !== 'pending') {
            return redirect()->route('custody_requests.show', $id)->with('error', __('Request cannot be edited after review.'));
        }

        // Inventory list
        $inventory = DB::table('Inventory')
            ->select([
                'InventoryID',
                'ItemName',
                'ItemQuantity',
                'ItemMeasuringUnit',
            ])
            ->orderBy('ItemName')
            ->get();

        // Dropdown data
        $qetaat = DB::table('Qetaa')->orderBy('QetaaName')->get();
        $eventTypes = DB::table('EventType')->orderBy('EventTypeName')->get();

        // Request items from DB
        $itemsRows = DB::table('CustodyRequestItems')
            ->where('RequestID', $id)
            ->orderBy('RequestItemID')
            ->get();

        // Build a plain array for JS (NO Blade map/json issues)
        $existingItems = $itemsRows->map(function ($it) {
            return [
                'inventory_id' => (int) $it->InventoryID,
                'name' => (string) $it->ItemNameSnapshot,
                'unit' => (string) ($it->ItemUnitSnapshot ?? ''),
                'qty' => (int) $it->QtyRequested,
            ];
        })->values()->all();

        return view('custody_requests.edit', [
            'requestRow' => $requestRow,
            'inventory' => $inventory,
            'qetaat' => $qetaat,
            'eventTypes' => $eventTypes,
            'existingItems' => $existingItems,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('custody.update', (int) $id);

        $personId = auth()->user()->PersonID ?? null;
        if (! $personId) {
            return redirect()->route('custody_requests.my')->with('error', __('Cannot determine current user.'));
        }

        // Ensure request exists + owned + pending
        $req = DB::table('CustodyRequests')
            ->where('RequestID', $id)
            ->where('PersonID', $personId)
            ->first();

        if (! $req) {
            return redirect()->route('custody_requests.my')->with('error', __('Request not found.'));
        }

        if ($req->Status !== 'pending') {
            return redirect()->route('custody_requests.show', $id)->with('error', __('Request cannot be edited after review.'));
        }

        // Validate
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',

            // optional dropdowns
            'qetaa_id' => 'nullable|integer',
            'event_type_id' => 'nullable|integer',

            'user_note' => 'nullable|string|max:500',

            // items array
            'items' => 'required|array|min:1',
            'items.*.inventory_id' => 'required|integer',
            'items.*.qty' => 'required|integer|min:1',
        ], [
            'items.required' => __('Please select at least one item.'),
            'items.min' => __('Please select at least one item.'),
        ]);

        // Optional FK existence checks (safe + friendly errors)
        if ($request->filled('qetaa_id')) {
            $ok = DB::table('Qetaa')->where('QetaaID', $request->qetaa_id)->exists();
            if (! $ok) {
                return back()->with('error', __('Invalid sector.'))->withInput();
            }
        }
        if ($request->filled('event_type_id')) {
            $ok = DB::table('EventType')->where('EventTypeID', $request->event_type_id)->exists();
            if (! $ok) {
                return back()->with('error', __('Invalid event type.'))->withInput();
            }
        }

        // Validate unique items
        $ids = array_map(fn ($x) => (int) $x['inventory_id'], $request->items);
        if (count($ids) !== count(array_unique($ids))) {
            return back()->with('error', __('The same item cannot be repeated more than once.'))->withInput();
        }

        // Load inventory snapshots for all items
        $invMap = DB::table('Inventory')
            ->whereIn('InventoryID', $ids)
            ->get()
            ->keyBy('InventoryID');

        if ($invMap->count() !== count($ids)) {
            return back()->with('error', __('An item does not exist in inventory.'))->withInput();
        }

        DB::beginTransaction();
        try {
            // Update main request
            DB::table('CustodyRequests')
                ->where('RequestID', $id)
                ->update([
                    'DateFrom' => $request->date_from,
                    'DateTo' => $request->date_to,
                    'QetaaID' => $request->qetaa_id ?: null,
                    'EventTypeID' => $request->event_type_id ?: null,
                    'UserNote' => $request->user_note,
                    'updated_at' => now(),
                ]);

            // Replace items: delete then insert (simple + reliable)
            DB::table('CustodyRequestItems')->where('RequestID', $id)->delete();

            $insertRows = [];
            foreach ($request->items as $it) {
                $invId = (int) $it['inventory_id'];
                $qty = (int) $it['qty'];

                $inv = $invMap[$invId];

                $insertRows[] = [
                    'RequestID' => $id,
                    'InventoryID' => $invId, // IMPORTANT: match your actual FK column name
                    'ItemNameSnapshot' => $inv->ItemName,
                    'ItemUnitSnapshot' => $inv->ItemMeasuringUnit ?? '',
                    'QtyRequested' => $qty,
                    'QtyApproved' => null,
                    'AdminItemNote' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('CustodyRequestItems')->insert($insertRows);

            DB::commit();

            return redirect()->route('custody_requests.show', $id)->with('success', __('Request updated successfully.'));
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', __('An error occurred while updating the request.'))->withInput();
        }

    }

    public function destroy($id)
    {
        $this->authorize('custody.delete', (int) $id);

        $personId = auth()->user()->PersonID ?? null;
        if (! $personId) {
            return back()->with('error', __('Cannot determine current user.'));
        }

        $req = DB::table('CustodyRequests')
            ->where('RequestID', $id)
            ->where('PersonID', $personId)
            ->first();

        if (! $req) {
            return redirect()->route('custody_requests.my')->with('error', __('Request not found.'));
        }

        if ($req->Status !== 'pending') {
            return redirect()->route('custody_requests.show', $id)
                ->with('error', __('Request cannot be deleted after review.'));
        }

        // If you have FK ON DELETE CASCADE from CustodyRequests -> CustodyRequestItems, this is enough.
        DB::table('CustodyRequests')->where('RequestID', $id)->delete();

        return redirect()->route('custody_requests.my')->with('success', __('Request deleted successfully.'));
    }
}
