<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FamilyMembersController extends Controller
{
    public function index(Request $request)
    {
        $familyMembers = DB::table('FamilyMembers as fm')
            ->leftJoin('PersonFamily as pf', 'pf.FamilyID', '=', 'fm.FamilyID')
            ->leftJoin('PersonInformation as pi', 'pi.PersonID', '=', 'pf.PersonID')
            ->leftJoin('Relations as r', 'r.RelationTypeID', '=', 'pf.RelationTypeID')
            ->select(
                'fm.FamilyID',
                'fm.FirstName',
                'fm.SecondName',
                'fm.ThirdName',
                'fm.FourthName',
                'fm.Email',
                'fm.MobileNumber',
                'fm.DateOfBirth',
                'fm.RaqamQawmy',
                DB::raw("CONCAT_WS(' ', fm.FirstName, fm.SecondName, fm.ThirdName, fm.FourthName) as FullName"),
                DB::raw('COUNT(DISTINCT pf.PersonID) as LinkedPersonsCount'),
                DB::raw("GROUP_CONCAT(DISTINCT CONCAT_WS(' ', pi.FirstName, pi.SecondName, pi.ThirdName, pi.FourthName) SEPARATOR ' | ') as LinkedPersons"),
                DB::raw("GROUP_CONCAT(DISTINCT r.RelationName SEPARATOR ' | ') as RelationNames")
            )
            ->groupBy(
                'fm.FamilyID',
                'fm.FirstName',
                'fm.SecondName',
                'fm.ThirdName',
                'fm.FourthName',
                'fm.Email',
                'fm.MobileNumber',
                'fm.DateOfBirth',
                'fm.RaqamQawmy'
            )
            ->orderBy('fm.FamilyID', 'DESC')
            ->get();

        return view('family-members.index', [
            'familyMembers' => $familyMembers,
        ]);
    }

    public function create()
    {
        $persons = DB::table('PersonInformation')
            ->select(
                'PersonID',
                DB::raw("CONCAT_WS(' ', FirstName, SecondName, ThirdName, FourthName) as FullName")
            )
            ->orderBy('FirstName')
            ->get();

        $relations = DB::table('Relations')
            ->orderBy('RelationName')
            ->get();

        return view('family-members.create', [
            'persons' => $persons,
            'relations' => $relations,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'second_name' => 'nullable|string|max:100',
            'third_name' => 'nullable|string|max:100',
            'fourth_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:150',
            'mobile_number' => 'nullable|digits:11',
            'date_of_birth' => 'nullable|date',
            'raqam_qawmy' => 'nullable|digits:14',
            'person_ids' => 'nullable|array',
            'person_ids.*' => 'nullable|integer|exists:PersonInformation,PersonID',
            'relation_type_ids' => 'nullable|array',
            'relation_type_ids.*' => 'nullable|integer|exists:Relations,RelationTypeID',
        ]);

        $validator->after(function ($validator) use ($request) {
            $personIds = $request->person_ids ?? [];
            $relationIds = $request->relation_type_ids ?? [];

            $count = max(count($personIds), count($relationIds));

            $pairs = [];

            for ($i = 0; $i < $count; $i++) {
                $personId = $personIds[$i] ?? null;
                $relationId = $relationIds[$i] ?? null;

                $personEmpty = empty($personId);
                $relationEmpty = empty($relationId);

                if ($personEmpty && $relationEmpty) {
                    continue;
                }

                if ($personEmpty xor $relationEmpty) {
                    $validator->errors()->add("assignment_$i", __('Person and relationship must both be selected in each row.'));
                }

                if (! $personEmpty && ! $relationEmpty) {
                    $pairKey = $personId.'-'.$relationId;

                    if (in_array($pairKey, $pairs)) {
                        $validator->errors()->add("assignment_duplicate_$i", __('The same person and relationship cannot be repeated more than once.'));
                    }

                    $pairs[] = $pairKey;
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if (! is_null($request->raqam_qawmy)) {
            $exists = DB::table('FamilyMembers')
                ->where('RaqamQawmy', $request->raqam_qawmy)
                ->exists();

            if ($exists) {
                return redirect()->back()->withErrors([
                    'raqam_qawmy' => __('National ID already exists for another family member'),
                ])->withInput();
            }
        }

        DB::beginTransaction();

        try {
            $familyId = DB::table('FamilyMembers')->insertGetId([
                'FirstName' => $request->first_name,
                'SecondName' => $request->second_name,
                'ThirdName' => $request->third_name,
                'FourthName' => $request->fourth_name,
                'Email' => $request->email,
                'MobileNumber' => $request->mobile_number,
                'DateOfBirth' => $request->date_of_birth,
                'RaqamQawmy' => $request->raqam_qawmy,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $personIds = $request->person_ids ?? [];
            $relationIds = $request->relation_type_ids ?? [];

            $count = max(count($personIds), count($relationIds));

            for ($i = 0; $i < $count; $i++) {
                $personId = $personIds[$i] ?? null;
                $relationId = $relationIds[$i] ?? null;

                if (empty($personId) && empty($relationId)) {
                    continue;
                }

                DB::table('PersonFamily')->insert([
                    'PersonID' => $personId,
                    'FamilyID' => $familyId,
                    'RelationTypeID' => $relationId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('family-members.index');
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('FamilyMembers store error', [
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', __('An error occurred while saving the family member'))->withInput();
        }
    }

    public function show($id)
    {
        $familyMember = DB::table('FamilyMembers as fm')
            ->where('fm.FamilyID', $id)
            ->select(
                'fm.*',
                DB::raw("CONCAT_WS(' ', fm.FirstName, fm.SecondName, fm.ThirdName, fm.FourthName) as FullName")
            )
            ->first();

        $assignments = DB::table('PersonFamily as pf')
            ->join('PersonInformation as pi', 'pi.PersonID', '=', 'pf.PersonID')
            ->join('Relations as r', 'r.RelationTypeID', '=', 'pf.RelationTypeID')
            ->where('pf.FamilyID', $id)
            ->select(
                'pf.PersonFamilyID',
                'pf.PersonID',
                'pf.RelationTypeID',
                'r.RelationName',
                DB::raw("CONCAT_WS(' ', pi.FirstName, pi.SecondName, pi.ThirdName, pi.FourthName) as PersonFullName")
            )
            ->orderBy('pf.PersonFamilyID', 'ASC')
            ->get();

        return view('family-members.show', [
            'familyMember' => $familyMember,
            'assignments' => $assignments,
        ]);
    }

    public function edit($id)
    {
        $familyMember = DB::table('FamilyMembers')
            ->where('FamilyID', $id)
            ->first();

        $persons = DB::table('PersonInformation')
            ->select(
                'PersonID',
                DB::raw("CONCAT_WS(' ', FirstName, SecondName, ThirdName, FourthName) as FullName")
            )
            ->orderBy('FirstName')
            ->get();

        $relations = DB::table('Relations')
            ->orderBy('RelationName')
            ->get();

        $assignments = DB::table('PersonFamily')
            ->where('FamilyID', $id)
            ->orderBy('PersonFamilyID', 'ASC')
            ->get();

        return view('family-members.edit', [
            'familyMember' => $familyMember,
            'persons' => $persons,
            'relations' => $relations,
            'assignments' => $assignments,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'second_name' => 'nullable|string|max:100',
            'third_name' => 'nullable|string|max:100',
            'fourth_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:150',
            'mobile_number' => 'nullable|digits:11',
            'date_of_birth' => 'nullable|date',
            'raqam_qawmy' => 'nullable|digits:14',
            'person_ids' => 'nullable|array',
            'person_ids.*' => 'nullable|integer|exists:PersonInformation,PersonID',
            'relation_type_ids' => 'nullable|array',
            'relation_type_ids.*' => 'nullable|integer|exists:Relations,RelationTypeID',
        ]);

        $validator->after(function ($validator) use ($request) {
            $personIds = $request->person_ids ?? [];
            $relationIds = $request->relation_type_ids ?? [];

            $count = max(count($personIds), count($relationIds));

            $pairs = [];

            for ($i = 0; $i < $count; $i++) {
                $personId = $personIds[$i] ?? null;
                $relationId = $relationIds[$i] ?? null;

                $personEmpty = empty($personId);
                $relationEmpty = empty($relationId);

                if ($personEmpty && $relationEmpty) {
                    continue;
                }

                if ($personEmpty xor $relationEmpty) {
                    $validator->errors()->add("assignment_$i", __('Person and relationship must both be selected in each row.'));
                }

                if (! $personEmpty && ! $relationEmpty) {
                    $pairKey = $personId.'-'.$relationId;

                    if (in_array($pairKey, $pairs)) {
                        $validator->errors()->add("assignment_duplicate_$i", __('The same person and relationship cannot be repeated more than once.'));
                    }

                    $pairs[] = $pairKey;
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if (! is_null($request->raqam_qawmy)) {
            $exists = DB::table('FamilyMembers')
                ->where('RaqamQawmy', $request->raqam_qawmy)
                ->where('FamilyID', '!=', $id)
                ->exists();

            if ($exists) {
                return redirect()->back()->withErrors([
                    'raqam_qawmy' => __('National ID already exists for another family member'),
                ])->withInput();
            }
        }

        DB::beginTransaction();

        try {
            DB::table('FamilyMembers')
                ->where('FamilyID', $id)
                ->update([
                    'FirstName' => $request->first_name,
                    'SecondName' => $request->second_name,
                    'ThirdName' => $request->third_name,
                    'FourthName' => $request->fourth_name,
                    'Email' => $request->email,
                    'MobileNumber' => $request->mobile_number,
                    'DateOfBirth' => $request->date_of_birth,
                    'RaqamQawmy' => $request->raqam_qawmy,
                    'updated_at' => now(),
                ]);

            DB::table('PersonFamily')
                ->where('FamilyID', $id)
                ->delete();

            $personIds = $request->person_ids ?? [];
            $relationIds = $request->relation_type_ids ?? [];

            $count = max(count($personIds), count($relationIds));

            for ($i = 0; $i < $count; $i++) {
                $personId = $personIds[$i] ?? null;
                $relationId = $relationIds[$i] ?? null;

                if (empty($personId) && empty($relationId)) {
                    continue;
                }

                DB::table('PersonFamily')->insert([
                    'PersonID' => $personId,
                    'FamilyID' => $id,
                    'RelationTypeID' => $relationId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('family-members.index');
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('FamilyMembers update error', [
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', __('An error occurred while updating the family member'))->withInput();
        }
    }

    public function delete($id)
    {
        $familyMember = DB::table('FamilyMembers')
            ->where('FamilyID', $id)
            ->select(
                'FamilyID',
                DB::raw("CONCAT_WS(' ', FirstName, SecondName, ThirdName, FourthName) as FullName")
            )
            ->first();

        $assignmentsCount = DB::table('PersonFamily')
            ->where('FamilyID', $id)
            ->count();

        return view('family-members.delete', [
            'familyMember' => $familyMember,
            'assignmentsCount' => $assignmentsCount,
        ]);
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            DB::table('PersonFamily')->where('FamilyID', $id)->delete();
            DB::table('FamilyMembers')->where('FamilyID', $id)->delete();

            DB::commit();

            return redirect()->route('family-members.index');
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('FamilyMembers destroy error', [
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', __('Family member cannot be deleted'));
        }
    }
}
