<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use \Illuminate\Http\Response;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;

use Session;

class SecretaryController extends Controller
{
/**
        * Display a listing of the resource.
        *
        * @return Response
        */
        public function index()
        {
            $documents = DB::table('Documents')->get();
            return view("secretary.index", array('documents' => $documents));
        }

        public function create()
        {
            return view("secretary.create");
        } 
        public function insert(Request  $request)
        {
            $lastDocumentID = DB::table('Documents')->orderBy('DocumentID','desc')->first();
            
            if($lastDocumentID==Null)
                $thisDocumentID = 1;
            else
                $thisDocumentID = $lastDocumentID->DocumentID + 1;

            DB::table('Documents')->insert(
                array(
                    'DocumentID' => $thisDocumentID,
                    'DocumentName' => $request -> document_name,
                    'DocumentLink' => $request -> document_content,
                    'created_at' => now(),
                    
                )
            );
            return redirect()->route('document.index');
        
        }

        public function edit($id)
        {
            $document = DB::table('Documents')->where('DocumentID', $id)->first();
            return view('secretary.edit', array('document' => $document));
        }

        public function update(Request $request, $id)
        {
            DB::table('Documents')
                ->where('DocumentID', $id)
                ->update(
                    array(
                        'DocumentName' => $request->document_name,
                        'DocumentLink' => $request->document_content,
                        'updated_at' => now(),
                    )
                );
            return redirect()->route('secretary.index');
        }

       public function deletes($id)
        {
            $document = DB::table('Documents')->where('DocumentID', $id)->first();
            return view("secretary.delete", array('document' => $document));
        }

        public function destroy($id)
        {

            $deleted = DB::table('Documents')->where('DocumentID',$id)->delete();
            return redirect()->route('secretary.index');
        }

       public function upload(Request $request)
{
    // 1) Validate inputs (keep your style, but for docs)
    $request->validate([
        'document_date' => 'required|date',
        'document_file' => 'required|mimes:pdf,doc,docx|max:10240', // 10MB
    ], [
        'document_file.mimes' => 'Only PDF, DOC, and DOCX are allowed.',
    ]);

    // 2) Init Google client (same as your code)
    $client = new Client();
    $client->setAuthConfig(storage_path('app/google-drive-credentials.json'));

    // ⚠️ Using session token after OAuth login (same pattern)
    $token = session('google_drive_token');
    if (!$token) {
        return back()->withErrors(['google' => 'Google Drive is not connected. Please sign in first.']);
    }
    $client->setAccessToken($token);

    // (Optional) refresh token if expired and we have a refresh_token
    if ($client->isAccessTokenExpired() && $client->getRefreshToken()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        session(['google_drive_token' => $client->getAccessToken()]);
    }

    // 3) Create Drive service
    $service = new Drive($client);

    // 4) Ensure the folder exists (find by name; create if not found)
    $folderName = 'LeadersMeetingDocument';
    $folderId = null;

    // Search for the folder in My Drive
    $list = $service->files->listFiles([
        'q' => "mimeType = 'application/vnd.google-apps.folder' and name = '{$folderName}' and trashed = false",
        'fields' => 'files(id, name)',
        'spaces' => 'drive',
        'pageSize' => 1,
    ]);

    if ($list->files && count($list->files) > 0) {
        $folderId = $list->files[0]->id;
    } else {
        // Create it if missing
        $folderFile = new DriveFile([
            'name' => $folderName,
            'mimeType' => 'application/vnd.google-apps.folder',
        ]);
        $createdFolder = $service->files->create($folderFile, ['fields' => 'id']);
        $folderId = $createdFolder->id;
    }

    // 5) File metadata/content
    $uploaded = $request->file('document_file');
    $originalExt = $uploaded->getClientOriginalExtension();
    $originalName = pathinfo($uploaded->getClientOriginalName(), PATHINFO_FILENAME);
    $safeDate = str_replace(['/', '\\', ':'], '-', $request->document_date);

    // e.g. LeadersMeetingDocument_2025-10-23_Report.docx
    $driveFileName = "{$folderName}_{$safeDate}_{$originalName}.{$originalExt}";

    $fileMetadata = new DriveFile([
        'name'    => $driveFileName,
        'parents' => [$folderId],
    ]);

    $content  = file_get_contents($uploaded->getRealPath());
    $mimeType = $uploaded->getMimeType();

    // 6) Upload
    $file = $service->files->create($fileMetadata, [
        'data' => $content,
        'mimeType' => $mimeType,
        'uploadType' => 'multipart',
        'fields' => 'id, name, webViewLink, webContentLink',
    ]);

    // 7) (Optional) Make it viewable by anyone with the link
    //    Remove this block if you want private access.
    try {
        $perm = new \Google\Service\Drive\Permission([
            'type' => 'anyone',
            'role' => 'reader',
        ]);
        $service->permissions->create($file->id, $perm);
    } catch (\Throwable $e) {
        // ignore if permission fails; continue
    }

    // 8) Save to DB (Documents: DocumentDate, DocumentLink)
    DB::table('Documents')->insert([
        'DocumentDate' => $request->document_date,          // e.g. 2025-10-23
        'DocumentLink' => $file->webViewLink ?? '',         // Drive view link
        'created_at'   => now(),
        'updated_at'   => now(),
    ]);

    return back()->with('success', '✅ Uploaded to Drive & saved link: ' . ($file->webViewLink ?? ''));
}


        /**
         * Find a Drive folder by name; if not found, create it.
         */
        private function getOrCreateFolder(Drive $service, string $folderName): string
        {
            $q = sprintf("name = '%s' and mimeType = 'application/vnd.google-apps.folder' and trashed = false", addslashes($folderName));
            $res = $service->files->listFiles([
                'q' => $q,
                'fields' => 'files(id, name)',
                'spaces' => 'drive',
                'pageSize' => 1,
            ]);

            if ($res->files && count($res->files) > 0) {
                return $res->files[0]->id;
            }

            $folderMeta = new DriveFile([
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder',
            ]);

            $folder = $service->files->create($folderMeta, ['fields' => 'id']);
            return $folder->id;
        }
}