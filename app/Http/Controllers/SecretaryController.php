<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;

class SecretaryController extends Controller
{
    /**
     * Show all documents.
     */
    public function index()
    {
        $documents = DB::table('Documents')->orderByDesc('created_at')->get();
        return view('secretary.index', compact('documents'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('secretary.create');
    }

    /**
     * Insert a new record manually (without upload).
     */
    public function insert(Request $request)
    {
        $validated = $request->validate([
            'document_name' => 'required|string|max:255',
            'document_content' => 'required|string',
        ]);

        $last = DB::table('Documents')->orderByDesc('DocumentID')->first();
        $id = $last ? $last->DocumentID + 1 : 1;

        DB::table('Documents')->insert([
            'DocumentID'   => $id,
            'DocumentName' => $validated['document_name'],
            'DocumentLink' => $validated['document_content'],
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return redirect()->route('secretary.index')->with('success', 'Document added.');
    }

    /**
     * Show edit form.
     */
    public function edit($id)
    {
        $document = DB::table('Documents')->where('DocumentID', $id)->first();
        return view('secretary.edit', compact('document'));
    }

    /**
     * Update record.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'document_name' => 'required|string|max:255',
            'document_content' => 'required|string',
        ]);

        DB::table('Documents')
            ->where('DocumentID', $id)
            ->update([
                'DocumentName' => $validated['document_name'],
                'DocumentLink' => $validated['document_content'],
                'updated_at'   => now(),
            ]);

        return redirect()->route('secretary.index')->with('success', 'Document updated.');
    }

    /**
     * Confirm deletion.
     */
    public function deletes($id)
    {
        $document = DB::table('Documents')->where('DocumentID', $id)->first();
        return view('secretary.delete', compact('document'));
    }

    /**
     * Delete record from DB.
     */
    public function destroy($id)
    {
        DB::table('Documents')->where('DocumentID', $id)->delete();
        return redirect()->route('secretary.index')->with('success', 'Document deleted.');
    }

    /**
     * Upload a file directly to Google Drive (backend-only OAuth).
     */
    public function upload(Request $request)
    {
        $request->validate([
            'document_date' => 'required|date',
            'document_file' => 'required|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx|max:10240',
        ]);

        // Initialize Google Client
        $client = new Client();
        $client->setAuthConfig(storage_path('app/google-drive-credentials.json'));
        $client->addScope(Drive::DRIVE);
        $client->setAccessType('offline');

        // Load saved token from backend
        $tokenPath = storage_path('app/drive_token.json');
        if (!file_exists($tokenPath)) {
            return back()->withErrors(['google' => 'Google Drive token not found. Run php artisan drive:authorize first.']);
        }

        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);

        // Refresh token if expired
        if ($client->isAccessTokenExpired() && $client->getRefreshToken()) {
            $newToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            file_put_contents($tokenPath, json_encode($newToken));
            $client->setAccessToken($newToken);
        }

        $service = new Drive($client);

        // Ensure Documents folder exists
        $folderId = $this->getOrCreateFolder($service, 'Documents');

        // Prepare file
        $uploaded = $request->file('document_file');
        $originalName = pathinfo($uploaded->getClientOriginalName(), PATHINFO_FILENAME);
        $ext = $uploaded->getClientOriginalExtension();
        $safeDate = str_replace(['/', '\\', ':'], '-', $request->document_date);
        $fileName = "Document_{$safeDate}_{$originalName}.{$ext}";

        $fileMetadata = new DriveFile([
            'name' => $fileName,
            'parents' => [$folderId],
        ]);

        $content = file_get_contents($uploaded->getRealPath());
        $mime = $uploaded->getMimeType();

        // Upload to Drive
        $file = $service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => $mime,
            'uploadType' => 'multipart',
            'fields' => 'id, name, webViewLink, webContentLink',
        ]);

        // Make public
        try {
            $permission = new Permission([
                'type' => 'anyone',
                'role' => 'reader',
            ]);
            $service->permissions->create($file->id, $permission);
        } catch (\Throwable $e) {
            // ignore permission failure
        }

        // Save to DB
        DB::table('Documents')->insert([
            'DocumentDate' => $request->document_date,
            'DocumentName' => $file->name,
            'DocumentLink' => $file->webViewLink ?? '',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return back()->with('success', '✅ File uploaded and saved: ' . ($file->webViewLink ?? ''));
    }

    /**
     * Get or create a Google Drive folder by name.
     */
    private function getOrCreateFolder(Drive $service, string $folderName): string
    {
        $query = sprintf(
            "mimeType='application/vnd.google-apps.folder' and name='%s' and trashed=false",
            addslashes($folderName)
        );
        $res = $service->files->listFiles([
            'q' => $query,
            'fields' => 'files(id, name)',
            'spaces' => 'drive',
            'pageSize' => 1,
        ]);

        if (!empty($res->files)) {
            return $res->files[0]->id;
        }

        $folderMeta = new DriveFile([
            'name' => $folderName,
            'mimeType' => 'application/vnd.google-apps.folder',
        ]);
        $folder = $service->files->create($folderMeta, ['fields' => 'id']);
        return $folder->id;
    }

    /**
 * Handles Google OAuth callback and stores the Drive token.
 */
public function driveCallback(Request $request)
{
    $code = $request->get('code');

    if (!$code) {
        return response()->json(['error' => 'No authorization code returned from Google.'], 400);
    }

    // Initialize Google Client
    $client = new \Google\Client();
    $client->setAuthConfig(storage_path('app/google-drive-credentials.json'));
    $client->setRedirectUri('https://shamandorascout.com/auth/google/callback');
    $client->setScopes([
        'https://www.googleapis.com/auth/drive',
        'https://www.googleapis.com/auth/drive.file',
        'https://www.googleapis.com/auth/drive.metadata'
    ]);
    $client->setAccessType('offline');
    $client->setPrompt('consent');

    // Exchange the code for tokens
    $token = $client->fetchAccessTokenWithAuthCode($code);

    if (isset($token['error'])) {
        return response()->json(['error' => $token['error_description'] ?? $token['error']], 400);
    }

    // Save the access + refresh tokens to a file
    file_put_contents(storage_path('app/drive_token.json'), json_encode($token));

    return redirect()->route('secretary.index')->with('success', '✅ Google Drive connected successfully!');
}

}