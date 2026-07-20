<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GuestsController extends Controller
{
    public function index(Request $request)
    {
        $guests = DB::table('Guests')
            ->leftJoin('PersonInformation', 'PersonInformation.PersonID', '=', 'Guests.PersonID')
            ->select(
                'Guests.GuestID',
                'Guests.FirstName',
                'Guests.SecondName',
                'Guests.ThirdName',
                'Guests.FourthName',
                'Guests.Email',
                'Guests.MobileNumber',
                'Guests.DateOfBirth',
                'Guests.RaqamQawmy',
                'Guests.PersonID',
                DB::raw("CONCAT_WS(' ', Guests.FirstName, Guests.SecondName, Guests.ThirdName, Guests.FourthName) as FullName"),
                DB::raw("CONCAT_WS(' ', PersonInformation.FirstName, PersonInformation.SecondName, PersonInformation.ThirdName, PersonInformation.FourthName) as PersonFullName")
            )
            ->orderBy('Guests.GuestID', 'DESC')
            ->get();

        return view('guests.index', ['guests' => $guests]);
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

        return view('guests.create', ['persons' => $persons]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'second_name' => 'nullable|string|max:100',
            'third_name' => 'nullable|string|max:100',
            'fourth_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:150',
            'mobile_number' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'raqam_qawmy' => 'nullable|digits:14',
            'person_id' => 'required|integer|exists:PersonInformation,PersonID',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if (! is_null($request->raqam_qawmy)) {
            $exists = DB::table('Guests')
                ->where('RaqamQawmy', $request->raqam_qawmy)
                ->exists();

            if ($exists) {
                return redirect()->back()->withErrors([
                    'raqam_qawmy' => 'الرقم القومي موجود بالفعل لضيف آخر',
                ])->withInput();
            }
        }

        DB::beginTransaction();

        try {
            DB::table('Guests')->insert([
                'FirstName' => $request->first_name,
                'SecondName' => $request->second_name,
                'ThirdName' => $request->third_name,
                'FourthName' => $request->fourth_name,
                'Email' => $request->email,
                'MobileNumber' => $request->mobile_number,
                'DateOfBirth' => $request->date_of_birth,
                'RaqamQawmy' => $request->raqam_qawmy,
                'PersonID' => $request->person_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('guests.index');
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Guests store error', [
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'حدث خطأ أثناء حفظ الضيف')->withInput();
        }
    }

    public function show($id)
    {
        $guest = DB::table('Guests')
            ->leftJoin('PersonInformation', 'PersonInformation.PersonID', '=', 'Guests.PersonID')
            ->where('Guests.GuestID', $id)
            ->select(
                'Guests.*',
                DB::raw("CONCAT_WS(' ', Guests.FirstName, Guests.SecondName, Guests.ThirdName, Guests.FourthName) as FullName"),
                DB::raw("CONCAT_WS(' ', PersonInformation.FirstName, PersonInformation.SecondName, PersonInformation.ThirdName, PersonInformation.FourthName) as PersonFullName")
            )
            ->first();

        return view('guests.show', ['guest' => $guest]);
    }

    public function edit($id)
    {
        $guest = DB::table('Guests')->where('GuestID', $id)->first();

        $persons = DB::table('PersonInformation')
            ->select(
                'PersonID',
                DB::raw("CONCAT_WS(' ', FirstName, SecondName, ThirdName, FourthName) as FullName")
            )
            ->orderBy('FirstName')
            ->get();

        return view('guests.edit', [
            'guest' => $guest,
            'persons' => $persons,
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
            'mobile_number' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'raqam_qawmy' => 'nullable|digits:14',
            'person_id' => 'required|integer|exists:PersonInformation,PersonID',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if (! is_null($request->raqam_qawmy)) {
            $exists = DB::table('Guests')
                ->where('RaqamQawmy', $request->raqam_qawmy)
                ->where('GuestID', '!=', $id)
                ->exists();

            if ($exists) {
                return redirect()->back()->withErrors([
                    'raqam_qawmy' => 'الرقم القومي موجود بالفعل لضيف آخر',
                ])->withInput();
            }
        }

        DB::beginTransaction();

        try {
            DB::table('Guests')
                ->where('GuestID', $id)
                ->update([
                    'FirstName' => $request->first_name,
                    'SecondName' => $request->second_name,
                    'ThirdName' => $request->third_name,
                    'FourthName' => $request->fourth_name,
                    'Email' => $request->email,
                    'MobileNumber' => $request->mobile_number,
                    'DateOfBirth' => $request->date_of_birth,
                    'RaqamQawmy' => $request->raqam_qawmy,
                    'PersonID' => $request->person_id,
                    'updated_at' => now(),
                ]);

            DB::commit();

            return redirect()->route('guests.index');
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Guests update error', [
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'حدث خطأ أثناء تعديل الضيف')->withInput();
        }
    }

    public function delete($id)
    {
        $guest = DB::table('Guests')
            ->where('GuestID', $id)
            ->select(
                'GuestID',
                DB::raw("CONCAT_WS(' ', FirstName, SecondName, ThirdName, FourthName) as FullName")
            )
            ->first();

        return view('guests.delete', ['guest' => $guest]);
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            DB::table('Guests')->where('GuestID', $id)->delete();

            DB::commit();

            return redirect()->route('guests.index');
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Guests destroy error', [
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'لا يمكن حذف الضيف');
        }
    }
}
