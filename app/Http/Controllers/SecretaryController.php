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
            // 1) Validate form
            $request->validate([
                'document_date' => ['required', 'date'],   // If your column is VARCHAR, it's still fine
                'document_file' => ['required', 'mimes:pdf,doc,docx', 'max:10240'], // 10 MB
            ], [
                'document_file.mimes' => 'Only PDF, DOC, and DOCX are allowed.',
            ]);

            // 2) Init Google Client (service account or OAuth token in session)
            $client = new Client();
            $client->setAuthConfig(storage_path('app/google-drive-credentials.json'));
            $client->setScopes([Drive::DRIVE_FILE]); // or Drive::DRIVE if you need wider scope

            // If you used OAuth web-flow before and keep token in session:
            if (session()->has('google_drive_token')) {
                $client->setAccessToken(session('google_drive_token'));
            }

            $service = new Drive($client);

            // 3) Ensure folder exists (get or create)
            $folderId = $this->getOrCreateFolder($service, 'LeadersMeetingDocument');

            // 4) Prepare file metadata
            $uploaded = $request->file('document_file');
            $originalName = $uploaded->getClientOriginalName();
            $safeDate = str_replace(['/', '\\', ':'], '-', $request->document_date);
            $driveFileName = 'LeadersMeetingDocument_' . $safeDate . '_' . $originalName;

            $fileMetadata = new DriveFile([
                'name' => $driveFileName,
                'parents' => [$folderId],
            ]);

            // 5) Upload file (multipart)
            $content = file_get_contents($uploaded->getRealPath());
            $mime = $uploaded->getMimeType();

            $file = $service->files->create(
                $fileMetadata,
                [
                    'data' => $content,
                    'mimeType' => $mime,
                    'uploadType' => 'multipart',
                    'fields' => 'id, name, webViewLink, webContentLink',
                ]
            );

            // 6) (Optional) Make link accessible to anyone with the link
            //    Comment this block out if you want it private.
            $permission = new Permission([
                'type' => 'anyone',
                'role' => 'reader',
            ]);
            $service->permissions->create($file->id, $permission);

            // 7) Save to DB
            DB::table('Documents')->insert([
                'DocumentDate' => $request->document_date,       // 'YYYY-MM-DD' from <input type="date">
                'DocumentLink' => $file->webViewLink ?? '',      // view link
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            return redirect()
                ->route('secretary.index')
                ->with('success', 'تم رفع الملف وحفظ الرابط بنجاح.');
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