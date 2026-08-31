<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class SecretaryController extends Controller
{
    public function index()
    {
        $documents = DB::table('Documents')->orderByDesc('created_at')->get();
        return view('secretary.index', compact('documents'));
    }

    public function create()
    {
        return view('secretary.create');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'document_date' => 'required|date',
            'document_file' => 'required|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx|max:10240',
        ]);

        // Store uploaded file locally
        $uploaded = $request->file('document_file');
        $ext = strtolower((string) $uploaded->getClientOriginalExtension());
        $allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'];
        if (! in_array($ext, $allowed, true)) {
            return back()->withErrors(['document_file' => __('Invalid file type.')])->withInput();
        }

        $fileName = Str::uuid()->toString().'.'.$ext;
        $path = $uploaded->storeAs('SecretaryDocuments', $fileName);

        // Save to DB: store file name and storage path
        DB::table('Documents')->insert([
            'DocumentDate' => $request->document_date,
            'DocumentName' => $fileName,
            'DocumentPath' => $path,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return back()->with('success', '✅ File uploaded and saved: ' . $path);
    }

    
    public function insert(Request $request)
    {
        // Backwards-compatible wrapper for older routes that point to "insert"
        return $this->upload($request);
    }

    public function edit($id)
    {
        $document = DB::table('Documents')->where('DocumentID', $id)->first();
        if (!$document) {
            return redirect()->route('secretary.index')->with('error', '❌ Document not found.');
        }

        return view('secretary.edit', ['document' => $document]);
    }

public function download($id)
    {
        $document = DB::table('Documents')->where('DocumentID', $id)->first();

        if (!$document || empty($document->DocumentPath)) {
            return back()->with('error', '❌ Document not found.');
        }

        $full = storage_path('app/' . $document->DocumentPath);
        if (!file_exists($full)) {
            return back()->with('error', '❌ File not found on disk.');
        }

        $downloadName = basename(str_replace(["\r", "\n", '"'], '', (string) ($document->DocumentName ?? basename($full))));

        return response()->download($full, $downloadName);
    }


    public function updates(Request $request, $id)
    {
        $request->validate([
            'document_name' => 'required|string|max:255',
        ]);

        DB::table('Documents')->where('DocumentID', $id)->update([
            'DocumentName' => $request->document_name,
            'updated_at'   => now(),
        ]);

        return redirect()->route('secretary.index')->with('status', '✅ Document updated successfully.');
    }


    public function delete($id)
    {
          $role = DB::table('Documents')->where('DocumentID', $id)->first();
            return view("secretary.delete", array('document' => $role));
    }

    // Old route handlers kept for compatibility (do nothing)
    public function deletes($id)
    {
        $role = DB::table('Documents')->where('DocumentID', $id)->first();
            return view("secretary.delete", array('document' => $role));
    }

    public function destroy($id)
    {

        $deleted = DB::table('Documents')->where('DocumentID',$id)->delete();

        return redirect()->route('secretary.index');}
    }