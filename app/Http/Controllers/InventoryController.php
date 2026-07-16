<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use \Illuminate\Http\Response;
use Session;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $inventory = DB::table('Inventory')->get();
        return view("inventory.index", array('inventory' => $inventory));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        // fetch enum values dynamically
        $units = $this->getEnumValues('Inventory', 'ItemMeasuringUnit');
        $categories = $this->getEnumValues('Inventory', 'Category');
        $locations = $this->getEnumValues('Inventory', 'Location');

        return view("inventory.create", compact(
            'units',
            'categories',
            'locations'
        ));
    }

    /**
     * Insert a newly created resource into DB.
     *
     * @param  Request $request
     * @return Response
     */
    public function insert(Request $request)
    {
        DB::table('Inventory')->insert(array(
            'ItemName'          => $request->item_name,
            'ItemQuantity'      => $request->item_quantity,
            'ItemMeasuringUnit' => $request->item_measuring_unit,
            'Category'          => $request->category,
            'Location'          => $request->location
        ));

        return redirect()->route('inventory.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        // fetch the record
        $inventory = DB::table('Inventory')
            ->where('InventoryID', $id)
            ->first();

        // if not found
        if (!$inventory) {
            return redirect()
                ->route('inventory.index')
                ->with('error', 'Item not found.');
        }

        // fetch enum values dynamically
        $units = $this->getEnumValues('Inventory', 'ItemMeasuringUnit');
        $categories = $this->getEnumValues('Inventory', 'Category');
        $locations = $this->getEnumValues('Inventory', 'Location');

        return view('inventory.edit', compact(
            'inventory',
            'units',
            'categories',
            'locations'
        ));
    }

    /**
     * Update the specified resource in DB.
     *
     * @param  Request $request
     * @param  int $id
     * @return Response
     */
    public function updates(Request $request, $id)
    {
        DB::table('Inventory')
            ->where('InventoryID', $id)
            ->update([
                'ItemName'          => $request->item_name,
                'ItemQuantity'      => $request->item_quantity,
                'ItemMeasuringUnit' => $request->item_measuring_unit,
                'Category'          => $request->category,
                'Location'          => $request->location
            ]);

        return redirect()->route('inventory.index');
    }

    /**
     * Show confirmation page before deleting.
     *
     * @param  int $id
     * @return Response
     */
    public function deletes($id)
    {
        $item = DB::table('Inventory')
            ->where('InventoryID', $id)
            ->first();

        return view("inventory.delete", array(
            'item' => $item,
            'title' => "حذف عنصر من المخزون"
        ));
    }

    /**
     * Remove the specified resource from DB.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        DB::table('Inventory')
            ->where('InventoryID', $id)
            ->delete();

        return redirect()->route('inventory.index');
    }

    /**
     * Helper: get enum values for dropdowns
     */
    private function getEnumValues($table, $column)
    {
        $type = DB::select(
            "SHOW COLUMNS FROM {$table} WHERE Field = '{$column}'"
        )[0]->Type;

        preg_match('/^enum\((.*)\)$/', $type, $matches);

        $enum = [];

        foreach (explode(',', $matches[1]) as $value) {
            $v = trim($value, "'");
            $enum[] = $v;
        }

        return $enum;
    }
}