<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PlaceController extends Controller
{
    public function index()
    {
        // Show places + location name
        $places = DB::table('Place')
            ->join('Locations', 'Place.LocationID', '=', 'Locations.LocationID')
            ->select('Place.*', 'Locations.LocationName as LocationName')
            ->get();

        return view("place.index", ['places' => $places]);
    }

    public function create()
    {
        $locations = DB::table('Locations')->get();
        return view("place.create", ['locations' => $locations]);
    }

    public function insert(Request $request)
    {
        $request->validate([
            'place_name'  => 'required|string|max:100',
            'location_id' => 'required|integer'
        ]);

        // Check location exists
        $loc = DB::table('Locations')->where('LocationID', $request->location_id)->first();
        if ($loc == null) {
            return redirect()->back()->with('status', 'الموقع غير موجود');
        }

        DB::table('Place')->insert([
            'PlaceName'  => $request->place_name,
            'LocationID' => $request->location_id
        ]);

        return redirect()->route('place.index')
            ->with('status', 'تم ادخال المكان بنجاح: ' . $request->place_name);
    }

    public function edit($id)
    {
        $place = DB::table('Place')->where('PlaceID', $id)->first();
        $locations = DB::table('Locations')->get();

        return view("place.edit", [
            'place'     => $place,
            'locations' => $locations,
            'title'     => "تعديل مكان"
        ]);
    }

    public function updates(Request $request, $id)
    {
        $request->validate([
            'place_name'  => 'required|string|max:100',
            'location_id' => 'required|integer'
        ]);

        // Check location exists
        $loc = DB::table('Locations')->where('LocationID', $request->location_id)->first();
        if ($loc == null) {
            return redirect()->back()->with('status', 'الموقع غير موجود');
        }

        DB::table('Place')
            ->where('PlaceID', $id)
            ->update([
                'PlaceName'  => $request->place_name,
                'LocationID' => $request->location_id
            ]);

        return redirect()->route('place.index')
            ->with('status', 'تم تعديل المكان بنجاح: ' . $request->place_name);
    }

    public function deletes($id)
    {
        $place = DB::table('Place')->where('PlaceID', $id)->first();
        return view("place.delete", ['place' => $place, 'title' => "حذف مكان"]);
    }

    public function destroy($id)
    {
        DB::table('Place')->where('PlaceID', $id)->delete();

        return redirect()->route('place.index')
            ->with('status', 'تم حذف المكان بنجاح');
    }
}