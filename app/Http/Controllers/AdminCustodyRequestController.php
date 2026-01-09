<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminCustodyRequestController extends Controller
{
    private function currentAdminPersonId()
    {
        return auth()->user()->PersonID ?? null;
    }

    public function index()
    {
        $requests = DB::table('CustodyRequests as R')
            ->leftJoin('Qetaa as Q', 'R.QetaaID', '=', 'Q.QetaaID')
            ->leftJoin('EventType as E', 'R.EventTypeID', '=', 'E.EventTypeID')
            ->leftJoin('PersonInformation as A', 'R.ReviewedBy', '=', 'A.PersonID')
            ->select([
                'R.*',
                'Q.QetaaName',
                'E.EventTypeName',
                   DB::raw("CONCAT(A.FirstName, ' ', A.SecondName) as ReviewerName"),
            ])
            ->orderByRaw("FIELD(R.Status,'pending','approved','rejected')")
            ->orderByDesc('R.created_at')
            ->get();

        return view('admin.custody-requests.index', compact('requests'));
    }

    public function show($id)
    {
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

        if (!$requestRow) {
            return redirect()->route('admin.custody_requests.index')->with('error', '❌ الطلب غير موجود.');
        }

        $items = DB::table('CustodyRequestItems')
            ->where('RequestID', $id)
            ->orderBy('RequestItemID')
            ->get();

        return view('admin.custody-requests.show', compact('requestRow', 'items'));
    }

    public function approve(Request $request, $id)
    {
        $adminPersonId = $this->currentAdminPersonId();
        if (!$adminPersonId) {
            return back()->with('error', '❌ لا يمكن تحديد الأدمن الحالي (PersonID).');
        }

        $requestRow = DB::table('CustodyRequests')->where('RequestID', $id)->first();
        if (!$requestRow) {
            return redirect()->route('admin.custody_requests.index')->with('error', '❌ الطلب غير موجود.');
        }

        if ($requestRow->Status !== 'pending') {
            return back()->with('error', '❌ لا يمكن اعتماد طلب تم مراجعته بالفعل.');
        }

        $request->validate([
            'approved_qty' => 'required|array|min:1',
            'admin_note'   => 'nullable|string|max:2000',
            'item_note'    => 'nullable|array',
        ], [
            'approved_qty.required' => 'من فضلك أدخل الكميات المعتمدة.',
        ]);

        $items = DB::table('CustodyRequestItems')
            ->where('RequestID', $id)
            ->orderBy('RequestItemID')
            ->get();

        if ($items->isEmpty()) {
            return back()->with('error', '❌ لا توجد أصناف داخل هذا الطلب.');
        }

        $reductions = [];
        $updates = [];

        foreach ($items as $it) {
            $itemId = (int) $it->RequestItemID;

            if (!array_key_exists($itemId, $request->approved_qty)) {
                return back()->with('error', '❌ من فضلك أدخل الكمية المعتمدة لكل الأصناف.')->withInput();
            }

            $approved = (int) $request->approved_qty[$itemId];
            $requestedQty = (int) $it->QtyRequested;

            if ($approved < 0) $approved = 0;

            // cannot exceed requested
            if ($approved > $requestedQty) {
                return back()->with('error', '❌ الكمية المعتمدة لا يمكن أن تكون أكبر من الكمية المطلوبة.')
                    ->withInput();
            }

            if ($approved < $requestedQty) {
                $reductions[] = "{$it->ItemNameSnapshot}: {$requestedQty} → {$approved}";
            }

            $note = null;
            if (is_array($request->item_note) && array_key_exists($itemId, $request->item_note)) {
                $note = trim((string)$request->item_note[$itemId]);
                if ($note === '') $note = null;
                if ($note && mb_strlen($note) > 500) $note = mb_substr($note, 0, 500);
            }

            $updates[] = [
                'RequestItemID' => $itemId,
                'QtyApproved'   => $approved,
                'AdminItemNote' => $note,
            ];
        }

        // Compose admin note that user will see
        $adminNote = trim((string)$request->admin_note);

        if (!empty($reductions)) {
            $reductionText = "تمت الموافقة على الطلب مع تعديل بعض الكميات:\n- " . implode("\n- ", $reductions);
            $adminNote = $adminNote ? ($adminNote . "\n\n" . $reductionText) : $reductionText;
        } else {
            if (!$adminNote) $adminNote = "تمت الموافقة على الطلب بالكامل.";
        }

        DB::beginTransaction();
        try {
            foreach ($updates as $u) {
                DB::table('CustodyRequestItems')
                    ->where('RequestItemID', $u['RequestItemID'])
                    ->update([
                        'QtyApproved'   => $u['QtyApproved'],
                        'AdminItemNote' => $u['AdminItemNote'],
                        'updated_at'    => now(),
                    ]);
            }

            DB::table('CustodyRequests')
                ->where('RequestID', $id)
                ->update([
                    'Status'     => 'approved',
                    'AdminNote'  => $adminNote,
                    'ReviewedBy' => $adminPersonId,
                    'ReviewedAt' => now(),
                    'updated_at' => now(),
                ]);

            DB::commit();
            return redirect()->route('admin.custody_requests.show', $id)
                ->with('success', '✅ تم اعتماد الطلب بنجاح.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', '❌ حدث خطأ أثناء اعتماد الطلب.')->withInput();
        }
    }

    public function reject(Request $request, $id)
    {
        $adminPersonId = $this->currentAdminPersonId();
        if (!$adminPersonId) {
            return back()->with('error', '❌ لا يمكن تحديد الأدمن الحالي (PersonID).');
        }

        $requestRow = DB::table('CustodyRequests')->where('RequestID', $id)->first();
        if (!$requestRow) {
            return redirect()->route('admin.custody_requests.index')->with('error', '❌ الطلب غير موجود.');
        }

        if ($requestRow->Status !== 'pending') {
            return back()->with('error', '❌ لا يمكن رفض طلب تم مراجعته بالفعل.');
        }

        $request->validate([
            'admin_note' => 'nullable|string|max:2000',
        ]);

        $adminNote = trim((string)$request->admin_note);
        if (!$adminNote) $adminNote = 'تم رفض الطلب.';

        DB::table('CustodyRequests')->where('RequestID', $id)->update([
            'Status'     => 'rejected',
            'AdminNote'  => $adminNote,
            'ReviewedBy' => $adminPersonId,
            'ReviewedAt' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.custody_requests.show', $id)->with('success', '✅ تم رفض الطلب.');
    }
}