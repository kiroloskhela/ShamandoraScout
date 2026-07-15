<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PersonExamMarkController extends Controller
{
    private function allowedPersonsSql(): string
    {
        return "
            SELECT DISTINCT
                pi.PersonID,
                pi.ShamandoraCode,
                CONCAT(
                    COALESCE(pi.FirstName, ''), ' ',
                    COALESCE(pi.SecondName, ''), ' ',
                    COALESCE(pi.ThirdName, ''), ' ',
                    COALESCE(pi.FourthName, '')
                ) AS PersonName,
                pi.FirstName,
                pi.SecondName,
                pi.ThirdName,
                pi.FourthName,
                q.QetaaName,
                pi.ScoutJoiningYear,
                sm.SanaMarhalaName,
                pi.RaqamQawmy,
                ppn.PersonPersonalMobileNumber,
                q.QetaaID,
                psm.SanaMarhalaID
            FROM PersonInformation pi
            LEFT JOIN PersonSanaMarhala psm ON pi.PersonID = psm.PersonID
            LEFT JOIN SanaMarhala sm ON sm.SanaMarhalaID = psm.SanaMarhalaID
            LEFT JOIN PersonQetaa pq ON pi.PersonID = pq.PersonID
            LEFT JOIN Qetaa q ON pq.QetaaID = q.QetaaID
            LEFT JOIN PersonPhoneNumbers ppn ON pi.PersonID = ppn.PersonID
            JOIN GroupQetaa gq ON gq.QetaaID = q.QetaaID
            JOIN PersonGroup pg2 ON pg2.GroupID = gq.GroupID
            WHERE q.QetaaID IN (
                SELECT gq2.QetaaID
                FROM GroupQetaa gq2
                WHERE gq2.GroupID IN (
                    SELECT pg3.GroupID
                    FROM PersonGroup pg3
                    WHERE pg3.PersonID = ?
                )
            )
        ";
    }

    private function allowedPersonIdsSql(): string
    {
        return "
            SELECT DISTINCT pi.PersonID
            FROM PersonInformation pi
            LEFT JOIN PersonQetaa pq ON pi.PersonID = pq.PersonID
            LEFT JOIN Qetaa q ON pq.QetaaID = q.QetaaID
            JOIN GroupQetaa gq ON gq.QetaaID = q.QetaaID
            JOIN PersonGroup pg2 ON pg2.GroupID = gq.GroupID
            WHERE q.QetaaID IN (
                SELECT gq2.QetaaID
                FROM GroupQetaa gq2
                WHERE gq2.GroupID IN (
                    SELECT pg3.GroupID
                    FROM PersonGroup pg3
                    WHERE pg3.PersonID = ?
                )
            )
        ";
    }

    private function allowedPersonExists($personId, $userId = null): bool
    {
        $userId = $userId ?? Auth::id();

        $person = DB::selectOne("
            SELECT PersonID
            FROM ({$this->allowedPersonIdsSql()}) allowed_people
            WHERE allowed_people.PersonID = ?
            LIMIT 1
        ", [$userId, $personId]);

        return $person !== null;
    }

    private function getAllowedMark($examMarkId, $userId = null)
    {
        $userId = $userId ?? Auth::id();

        return DB::selectOne("
            SELECT
                em.ExamMarkID,
                em.PersonID,
                em.ServentID,
                em.QetaaID,
                em.SanaMarhalaID,
                em.TheoreticalMark,
                em.PracticalMark,
                em.ExamDate,
                em.Note,
                q.QetaaName,
                sm.SanaMarhalaName,
                CONCAT(
                    COALESCE(p.FirstName, ''), ' ',
                    COALESCE(p.SecondName, ''), ' ',
                    COALESCE(p.ThirdName, ''), ' ',
                    COALESCE(p.FourthName, '')
                ) AS PersonName,
                CONCAT(
                    COALESCE(s.FirstName, ''), ' ',
                    COALESCE(s.SecondName, ''), ' ',
                    COALESCE(s.ThirdName, ''), ' ',
                    COALESCE(s.FourthName, '')
                ) AS ServentName
            FROM PersonExamMark em
            LEFT JOIN PersonInformation p ON p.PersonID = em.PersonID
            LEFT JOIN PersonInformation s ON s.PersonID = em.ServentID
            LEFT JOIN Qetaa q ON q.QetaaID = em.QetaaID
            LEFT JOIN SanaMarhala sm ON sm.SanaMarhalaID = em.SanaMarhalaID
            WHERE em.ExamMarkID = ?
              AND em.PersonID IN ({$this->allowedPersonIdsSql()})
            LIMIT 1
        ", [$examMarkId, $userId]);
    }

    private function markRules(): array
    {
        return [
            'person_id' => ['required', 'integer'],
            'qetaa_id' => ['required', 'integer', Rule::exists('Qetaa', 'QetaaID')],
            'sana_marhala_id' => ['required', 'integer', Rule::exists('SanaMarhala', 'SanaMarhalaID')],
            'theoretical_mark' => ['required', 'integer', 'min:0', 'max:999'],
            'practical_mark' => ['required', 'integer', 'min:0', 'max:999'],
            'exam_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function index()
    {
        $userId = Auth::id();

        $marks = DB::select("
            SELECT
                em.ExamMarkID,
                em.PersonID,
                em.ServentID,
                em.QetaaID,
                em.SanaMarhalaID,
                em.TheoreticalMark,
                em.PracticalMark,
                em.ExamDate,
                em.Note,
                q.QetaaName,
                sm.SanaMarhalaName,
                CONCAT(
                    COALESCE(p.FirstName, ''), ' ',
                    COALESCE(p.SecondName, ''), ' ',
                    COALESCE(p.ThirdName, ''), ' ',
                    COALESCE(p.FourthName, '')
                ) AS PersonName,
                CONCAT(
                    COALESCE(s.FirstName, ''), ' ',
                    COALESCE(s.SecondName, ''), ' ',
                    COALESCE(s.ThirdName, ''), ' ',
                    COALESCE(s.FourthName, '')
                ) AS ServentName
            FROM PersonExamMark em
            LEFT JOIN PersonInformation p ON p.PersonID = em.PersonID
            LEFT JOIN PersonInformation s ON s.PersonID = em.ServentID
            LEFT JOIN Qetaa q ON q.QetaaID = em.QetaaID
            LEFT JOIN SanaMarhala sm ON sm.SanaMarhalaID = em.SanaMarhalaID
            WHERE em.PersonID IN ({$this->allowedPersonIdsSql()})
            ORDER BY em.ExamDate DESC, em.ExamMarkID DESC
        ", [$userId]);

        return view('personexammark.index', ['marks' => $marks]);
    }

    public function create()
    {
        return view('personexammark.create', [
            'qetaas' => DB::table('Qetaa')->orderBy('QetaaName')->get(),
            'sanaMarhalas' => DB::table('SanaMarhala')->orderBy('SanaMarhalaName')->get(),
        ]);
    }

    public function insert(Request $request)
    {
        $data = $request->validate($this->markRules());
        $userId = Auth::id();

        if (!$this->allowedPersonExists($data['person_id'], $userId)) {
            return redirect()->back()->withInput()->with('error', 'هذا الشخص غير متاح لك');
        }

        DB::table('PersonExamMark')->insert([
            'PersonID' => $data['person_id'],
            'ServentID' => $userId,
            'QetaaID' => $data['qetaa_id'],
            'SanaMarhalaID' => $data['sana_marhala_id'],
            'TheoreticalMark' => $data['theoretical_mark'],
            'PracticalMark' => $data['practical_mark'],
            'ExamDate' => $data['exam_date'],
            'Note' => $data['note'] ?? null,
        ]);

        return redirect()->route('personexammark.index')
            ->with('status', 'تم تسجيل درجات الامتحان بنجاح');
    }

    public function edit($id)
    {
        $mark = $this->getAllowedMark($id);

        if (!$mark) {
            abort(403, 'غير مسموح لك بالوصول لهذا السجل');
        }

        return view('personexammark.edit', [
            'mark' => $mark,
            'qetaas' => DB::table('Qetaa')->orderBy('QetaaName')->get(),
            'sanaMarhalas' => DB::table('SanaMarhala')->orderBy('SanaMarhalaName')->get(),
        ]);
    }

    public function updates(Request $request, $id)
    {
        $mark = $this->getAllowedMark($id);
        if (!$mark) {
            abort(403, 'غير مسموح لك بتعديل هذا السجل');
        }

        $data = $request->validate([
            'qetaa_id' => ['required', 'integer', Rule::exists('Qetaa', 'QetaaID')],
            'sana_marhala_id' => ['required', 'integer', Rule::exists('SanaMarhala', 'SanaMarhalaID')],
            'theoretical_mark' => ['required', 'integer', 'min:0', 'max:999'],
            'practical_mark' => ['required', 'integer', 'min:0', 'max:999'],
            'exam_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        DB::table('PersonExamMark')
            ->where('ExamMarkID', $id)
            ->update([
                'QetaaID' => $data['qetaa_id'],
                'SanaMarhalaID' => $data['sana_marhala_id'],
                'TheoreticalMark' => $data['theoretical_mark'],
                'PracticalMark' => $data['practical_mark'],
                'ExamDate' => $data['exam_date'],
                'Note' => $data['note'] ?? null,
            ]);

        return redirect()->route('personexammark.index')
            ->with('status', 'تم تعديل درجات الامتحان بنجاح');
    }

    public function deletes($id)
    {
        $mark = $this->getAllowedMark($id);
        if (!$mark) {
            abort(403, 'غير مسموح لك بحذف هذا السجل');
        }

        return view('personexammark.delete', ['mark' => $mark]);
    }

    public function destroy($id)
    {
        $mark = $this->getAllowedMark($id);
        if (!$mark) {
            abort(403, 'غير مسموح لك بحذف هذا السجل');
        }

        DB::table('PersonExamMark')->where('ExamMarkID', $id)->delete();

        return redirect()->route('personexammark.index')
            ->with('status', 'تم حذف سجل الدرجات بنجاح');
    }

    public function searchPersons(Request $request)
    {
        $userId = Auth::id();
        $term = \App\Support\LikeSearch::fromRequest($request, ['search', 'q']);

        try {
            $bindings = [$userId];
            $whereSql = '1=1';
            if ($term !== null) {
                $fragment = \App\Support\LikeSearch::sqlOr(
                    \App\Support\LikeSearch::allowedPeopleColumns(),
                    $term
                );
                $whereSql = $fragment['sql'];
                $bindings = array_merge($bindings, $fragment['bindings']);
            }

            $persons = DB::select("
                SELECT *
                FROM ({$this->allowedPersonsSql()}) allowed_people
                WHERE {$whereSql}
                ORDER BY allowed_people.ShamandoraCode ASC
                LIMIT 20
            ", $bindings);

            return response()->json($persons);
        } catch (\Throwable $e) {
            Log::error('personexammark searchPersons failed', [
                'message' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Search failed'], 500);
        }
    }
}
