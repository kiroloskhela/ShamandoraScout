<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;

class SeasonController extends Controller
{
    public function index()
    {
        $seasons = DB::table('Season')->get();
        return view("season.index", ['seasons' => $seasons]);
    }

    public function create()
    {
        return view("season.create");
    }

    public function insert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'season_name' => 'nullable|string|max:255',
            'season_year' => 'required|integer'
        ]);

        $seasonName = $request->season_name;
    if (empty($seasonName)) {
        $seasonName = 'موسم ' . $request->season_year;
    }

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

      DB::table('Season')->insert([
   'SeasonName' => $seasonName,
    'SeasonYear' => $request->season_year
]);


            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return view('person.entry-error');
        }

        return redirect()->route('season.index')->with('success', 'Season created successfully!');
    }





    public function edit($id)
    {
        $season = DB::table('Season')->where('SeasonID', $id)->first();
        if (!$season) {
            abort(404);
        }
        return view("season.edit", ['season' => $season]);
    }

    public function updates(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'season_name' => 'required|string|max:100',
            'season_year' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            DB::table('Season')->where('SeasonID', $id)->update([
           'SeasonName' => $request->season_name,
    'SeasonYear' => $request->season_year
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return view('person.entry-error-repeat-trial');
        }

        return redirect()->route('season.index')->with('success', 'Season updated successfully!');
    }

    public function deletes($id)
    {
        $season = DB::table('Season')->where('SeasonID', $id)->first();
        if (!$season) {
            abort(404);
        }
        return view("season.delete", ['season' => $season]);
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            DB::table('Season')->where('SeasonID', $id)->delete();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return view('person.entry-error-repeat-trial');
        }

        return redirect()->route('season.index')->with('success', 'Season deleted successfully!');
    }
}