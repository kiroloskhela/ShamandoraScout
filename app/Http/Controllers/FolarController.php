<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class FolarController extends LookupTableController
{
    protected string $lookupKey = 'folar';

    public function destroy($id)
    {
        $inUse = DB::table('PersonFolar')->where('FolarID', $id)->exists();
        if ($inUse) {
            return redirect()
                ->route('folar.index')
                ->withErrors(['folar' => __('Cannot delete a scarf that is assigned to members.')]);
        }

        return parent::destroy($id);
    }
}
