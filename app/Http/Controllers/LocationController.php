<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    public function index()
    {
        $locations = DB::table('Locations')->get();
        return view("locations.index", ['locations' => $locations]);
    }

    public function create()
    {
        return view("locations.create");
    }

    public function insert(Request $request)
    {
        $request->validate([
            'LocationName' => 'required|string|max:100',
        ]);

        DB::table('Locations')->insert([
            'LocationID' => \App\Support\ManualPrimaryKey::next('Locations', 'LocationID'),
            'LocationName' => $request->LocationName,
        ]);

        return redirect()->route('locations.index')
            ->with('status', 'تم ادخال الموقع بنجاح: ' . $request->LocationName);
    }


    public function edit($id)
    {
        $location = DB::table('Locations')->where('LocationID', $id)->first();
        return view("locations.edit", ['location' => $location, 'title' => "تعديل موقع"]);
    }

    public function updates(Request $request, $id)
    {
        $request->validate([
            'LocationName' => 'required|string|max:100',
        ]);

        DB::table('Locations')
            ->where('LocationID', $id)
            ->update(['LocationName' => $request->LocationName]);

        return redirect()->route('locations.index')
            ->with('status', 'تم تعديل الموقع بنجاح: ' . $request->LocationName);
    }


    public function deletes($id)
    {
        $location = DB::table('Locations')->where('LocationID', $id)->first();
        return view("locations.delete", ['location' => $location, 'title' => "حذف موقع"]);
    }

    public function destroy($id)
    {
        // If PlaceTypes references this location, delete may fail (FK restrict)
        DB::table('Locations')->where('LocationID', $id)->delete();

        return redirect()->route('locations.index')
            ->with('status', 'تم حذف الموقع بنجاح');
    }
}