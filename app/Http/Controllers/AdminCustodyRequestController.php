<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminCustodyRequestController extends Controller
{
    private function currentAdminPersonId()
    {
        $user = auth()->user();

        return $user?->PersonID;
    }

    public function index()
    {
        $this->authorize('custody.viewAdmin');

        $requests = DB::table('CustodyRequests as R')
            ->leftJoin('Qetaa as Q', 'R.QetaaID', '=', 'Q.QetaaID')
            ->leftJoin('EventType as E', 'R.EventTypeID', '=', 'E.EventTypeID')
            ->leftJoin('PersonInformation as A', 'R.ReviewedBy', '=', 'A.PersonID')
            ->leftJoin('PersonInformation as U', 'R.PersonID', '=', 'U.PersonID')
            ->select([
                'R.*',
                'Q.QetaaName',
                'E.EventTypeName',
                DB::raw("CONCAT(A.FirstName, ' ', A.SecondName) as ReviewerName"),
                DB::raw("CONCAT(U.FirstName, ' ', U.SecondName, ' ', COALESCE(U.ThirdName,'')) as UserName"),
            ])
            ->orderByRaw("FIELD(R.Status,'pending','approved','rejected')")
            ->orderByDesc('R.created_at')
            ->get();

        return view('admin.custody-requests.index', compact('requests'));
    }

    public function show($id)
    {
        $this->authorize('custody.viewAdmin');
        $this->authorize('custody.view', (int) $id);

        $requestRow = DB::table('CustodyRequests as R')
            ->leftJoin('Qetaa as Q', 'R.QetaaID', '=', 'Q.QetaaID')
            ->leftJoin('EventType as E', 'R.EventTypeID', '=', 'E.EventTypeID')
            ->leftJoin('PersonInformation as A', 'R.ReviewedBy', '=', 'A.PersonID')
            ->where('R.RequestID', $id)
            ->select([
                'R.*',
                'Q.QetaaName',
                'E.EventTypeName',
                DB::raw("CONCAT(A.FirstName, ' ', A.SecondName) as ReviewerName"),
            ])
            ->first();

        if (! $requestRow) {
            return redirect()->route('admin.custody_requests.index')->with('error', __('Request not found.'));
        }

        $items = DB::table('CustodyRequestItems')
            ->where('RequestID', $id)
            ->orderBy('RequestItemID')
            ->get();

        return view('admin.custody-requests.show', compact('requestRow', 'items'));
    }

    public function approve(Request $request, $id)
    {
        $this->authorize('custody.review');

        $adminPersonId = $this->currentAdminPersonId();
        if (! $adminPersonId) {
            return back()->with('error', __('Cannot determine current admin (PersonID).'));
        }

        $requestRow = DB::table('CustodyRequests')->where('RequestID', $id)->first();
        if (! $requestRow) {
            return redirect()->route('admin.custody_requests.index')->with('error', __('Request not found.'));
        }

        $validated = $request->validate([
            'approved_qty' => 'required|array|min:1',
            'approved_qty.*' => 'required|integer|min:0',
            'admin_note' => 'nullable|string|max:2000',
            'item_note' => 'nullable|array',
            'item_note.*' => 'nullable|string|max:500',
        ], [
            'approved_qty.required' => __('Please enter approved quantities.'),
        ]);

        $items = DB::table('CustodyRequestItems')
            ->where('RequestID', $id)
            ->orderBy('RequestItemID')
            ->get();

        if ($items->isEmpty()) {
            return back()->with('error', __('No items found in this request.'));
        }

        $reductions = [];
        $updates = [];

        foreach ($items as $it) {
            $itemId = (int) $it->RequestItemID;

            if (! array_key_exists($itemId, $validated['approved_qty'])) {
                return back()->withErrors(['approved_qty' => __('Please enter the approved quantity for each item.')])->withInput();
            }

            $approved = (int) $validated['approved_qty'][$itemId];
            $requestedQty = (int) $it->QtyRequested;

            if ($approved < 0) {
                $approved = 0;
            }

            // cannot exceed requested
            if ($approved > $requestedQty) {
                return back()->withErrors(['approved_qty.'.$itemId => __('Approved quantity exceeds requested quantity.')])->withInput();
            }

            if ($approved < $requestedQty) {
                $reductions[] = "{$it->ItemNameSnapshot}: {$requestedQty} → {$approved}";
            }

            $note = null;
            if (is_array($validated['item_note'] ?? []) && array_key_exists($itemId, $validated['item_note'])) {
                $note = trim((string) $validated['item_note'][$itemId]);
                if ($note === '') {
                    $note = null;
                }
                if ($note && mb_strlen($note) > 500) {
                    $note = mb_substr($note, 0, 500);
                }
            }

            $updates[] = [
                'RequestItemID' => $itemId,
                'QtyApproved' => $approved,
                'AdminItemNote' => $note,
            ];
        }

        // Compose admin note that user will see
        $adminNote = trim((string) $validated['admin_note']);

        if (! empty($reductions)) {
            $reductionText = __('Request approved with quantity adjustments:')."\n- ".implode("\n- ", $reductions);
            $adminNote = $adminNote ? ($adminNote."\n\n".$reductionText) : $reductionText;
        } else {
            if (! $adminNote) {
                $adminNote = __('Request approved in full.');
            }
        }

        DB::beginTransaction();
        try {
            foreach ($updates as $u) {
                DB::table('CustodyRequestItems')
                    ->where('RequestItemID', $u['RequestItemID'])
                    ->update([
                        'QtyApproved' => $u['QtyApproved'],
                        'AdminItemNote' => $u['AdminItemNote'],
                        'updated_at' => now(),
                    ]);
            }

            // Atomic update to prevent TOCTOU: ensure status is still pending
            $affected = DB::table('CustodyRequests')
                ->where('RequestID', $id)
                ->where('Status', 'pending')
                ->update([
                    'Status' => 'approved',
                    'AdminNote' => $adminNote,
                    'ReviewedBy' => $adminPersonId,
                    'ReviewedAt' => now(),
                    'updated_at' => now(),
                ]);

            if ($affected === 0) {
                DB::rollBack();

                return back()->with('error', __('Cannot approve a request that has already been reviewed.'))->withInput();
            }

            DB::commit();

            NotificationController::sendToUserId(
                $requestRow->PersonID,
                __('Custody request approved'),
                __('Your custody request from :from to :to was approved.', [
                    'from' => $requestRow->date_from,
                    'to' => $requestRow->date_to,
                ])
            );

            return redirect()->route('admin.custody_requests.show', $id)
                ->with('success', __('Request approved successfully.'));
        } catch (\Throwable $e) {
            Log::error('Error approving custody request', ['exception' => $e, 'requestId' => $id]);
            DB::rollBack();

            return back()->with('error', __('An error occurred while approving the request.'))->withInput();
        }
    }

    public function reject(Request $request, $id)
    {
        $this->authorize('custody.review');

        $adminPersonId = $this->currentAdminPersonId();
        if (! $adminPersonId) {
            return back()->with('error', __('Cannot determine current admin (PersonID).'));
        }

        $requestRow = DB::table('CustodyRequests')->where('RequestID', $id)->first();
        if (! $requestRow) {
            return redirect()->route('admin.custody_requests.index')->with('error', __('Request not found.'));
        }

        if ($requestRow->Status !== 'pending') {
            return back()->with('error', __('Cannot reject a request that has already been reviewed.'));
        }

        $validated = $request->validate([
            'admin_note' => 'nullable|string|max:2000',
        ]);

        $adminNote = trim((string) $validated['admin_note']);
        if (! $adminNote) {
            $adminNote = __('Request rejected.');
        }

        try {
            $affected = DB::table('CustodyRequests')
                ->where('RequestID', $id)
                ->where('Status', 'pending')
                ->update([
                    'Status' => 'rejected',
                    'AdminNote' => $adminNote,
                    'ReviewedBy' => $adminPersonId,
                    'ReviewedAt' => now(),
                    'updated_at' => now(),
                ]);

            if ($affected === 0) {
                return back()->with('error', __('Cannot reject a request that has already been reviewed.'));
            }

            // 🔔 Send Notification
            NotificationController::sendToUserId(
                $requestRow->PersonID,
                __('Custody request rejected'),
                __('Your custody request from :from to :to was rejected. Reason: :reason', [
                    'from' => $requestRow->date_from,
                    'to' => $requestRow->date_to,
                    'reason' => $adminNote,
                ])
            );

            return redirect()->route('admin.custody_requests.show', $id)->with('success', __('Request rejected.'));
        } catch (\Throwable $e) {
            Log::error('Error rejecting custody request', ['exception' => $e, 'requestId' => $id]);

            return back()->with('error', __('An error occurred while rejecting the request.'))->withInput();
        }
    }
}
