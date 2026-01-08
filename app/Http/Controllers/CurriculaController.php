<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CurriculaController extends Controller
{
    public function index()
    {
        $curricula = DB::table('Curricula')
            ->join('CurriculaCategory', 'Curricula.CurriculaCategoryID', '=', 'CurriculaCategory.CurriculaCategoryID')
            ->join('Marhala', 'Curricula.MarhalaID', '=', 'Marhala.MarhalaID')
            ->orderByDesc('Curricula.created_at')
            ->get();

        return view('Curricula.index', compact('curricula'));
    }

    public function create()
    {
        $categories = DB::table('CurriculaCategory')->get();
        $marhalat   = DB::table('Marhala')->get();

        return view('Curricula.create', compact('categories', 'marhalat'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'curricula_name'        => 'required|string|max:255',
            'curricula_file'        => 'required|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx|max:10240',
            'curricula_category_id' => 'required|integer',
            'marhala_id'            => 'required|integer',
        ]);

        // File handling
        $uploaded = $request->file('curricula_file');
        $ext = $uploaded->getClientOriginalExtension();

        $safeName = str_replace(['/', '\\', ':'], '-', $request->curricula_name);
        $fileName = "{$safeName}." . $ext;

        $path = $uploaded->storeAs('CurriculaDocuments', $fileName);

        // Insert into DB
        DB::table('Curricula')->insert([
            'CurriculaName'        => $request->curricula_name,
            'CurriculaPath'        => $path,
            'CurriculaCategoryID'  => $request->curricula_category_id,
            'MarhalaID'            => $request->marhala_id,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        return back()->with('success', '✅ Curriculum uploaded successfully.');
    }

    // Backward-compatible wrapper
    public function insert(Request $request)
    {
        return $this->upload($request);
    }

    public function edit($id)
    {
        $curriculum = DB::table('Curricula')->where('CurriculaID', $id)->first();
        if (!$curriculum) {
            return redirect()->route('Curricula.index')->with('error', '❌ Curriculum not found.');
        }

        $categories = DB::table('CurriculaCategory')->get();
        $marhalat   = DB::table('Marhala')->get();

        return view('Curricula.edit', compact('curriculum', 'categories', 'marhalat'));
    }

    public function download($id)
    {
        $curriculum = DB::table('Curricula')->where('CurriculaID', $id)->first();

        if (!$curriculum || empty($curriculum->CurriculaPath)) {
            return back()->with('error', '❌ File not found.');
        }

        $full = storage_path('app/' . $curriculum->CurriculaPath);
        if (!file_exists($full)) {
            return back()->with('error', '❌ File missing on disk.');
        }

        return response()->download($full, basename($full));
    }

    public function updates(Request $request, $id)
    {
        $request->validate([
            'curricula_name'        => 'required|string|max:255',
            'curricula_category_id' => 'required|integer',
            'marhala_id'            => 'required|integer',
        ]);

        DB::table('Curricula')->where('CurriculaID', $id)->update([
            'CurriculaName'       => $request->curricula_name,
            'CurriculaCategoryID' => $request->curricula_category_id,
            'MarhalaID'           => $request->marhala_id,
            'updated_at'          => now(),
        ]);

        return redirect()->route('Curricula.index')->with('status', '✅ Curriculum updated successfully.');
    }

    public function delete($id)
    {
        $curriculum = DB::table('Curricula')->where('CurriculaID', $id)->first();
        return view('Curricula.delete', compact('curriculum'));
    }

    // Backward-compatibility
    public function deletes($id)
    {
        return $this->delete($id);
    }

    public function destroy($id)
    {
        $curriculum = DB::table('Curricula')->where('CurriculaID', $id)->first();

        if ($curriculum && !empty($curriculum->CurriculaPath)) {
            Storage::delete($curriculum->CurriculaPath);
        }

        DB::table('Curricula')->where('CurriculaID', $id)->delete();

        return redirect()->route('curricula.index')->with('status', '🗑️ Curriculum deleted.');
    }
}