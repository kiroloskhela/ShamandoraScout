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

        // Initialize Google Client
        $client = new Client();
        $client->setAuthConfig(storage_path('app/google-drive-credentials.json'));
        $client->addScope(Drive::DRIVE);
        $client->setAccessType('offline');

        // Load saved token
        $tokenPath = storage_path('app/drive_token.json');
        if (!file_exists($tokenPath)) {
            return back()->withErrors(['google' => 'Google Drive token not found. Run php artisan drive:authorize first.']);
        }

        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);

        // Refresh if needed
        if ($client->isAccessTokenExpired() && $client->getRefreshToken()) {
            $newToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            file_put_contents($tokenPath, json_encode($newToken));
            $client->setAccessToken($newToken);
        }

        $service = new Drive($client);

        // Ensure Documents folder exists
        $folderId = $this->getOrCreateFolder($service, 'Documents');

        // Upload file
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

        $file = $service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => $mime,
            'uploadType' => 'multipart',
            'fields' => 'id, name, webViewLink, webContentLink',
        ]);

        // Make file public
        try {
            $perm = new Permission([
                'type' => 'anyone',
                'role' => 'reader',
            ]);
            $service->permissions->create($file->id, $perm);
        } catch (\Throwable $e) {
            // Ignore permission error
        }

        // Save to DB (no DocumentName column)
        DB::table('Documents')->insert([
            'DocumentDate' => $request->document_date,
            'DocumentLink' => $file->webViewLink ?? '',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return back()->with('success', '✅ File uploaded and saved: ' . ($file->webViewLink ?? ''));
    }

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
     * Google OAuth callback (token saver)
     */
    public function driveCallback(Request $request)
    {
        $code = $request->get('code');

        if (!$code) {
            return response()->json(['error' => 'No authorization code returned from Google.'], 400);
        }

        $client = new Client();
        $client->setAuthConfig(storage_path('app/google-drive-credentials.json'));
        $client->setRedirectUri('https://shamandorascout.com/auth/google/callback');
        $client->setScopes([
            'https://www.googleapis.com/auth/drive',
            'https://www.googleapis.com/auth/drive.file',
            'https://www.googleapis.com/auth/drive.metadata'
        ]);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            return response()->json(['error' => $token['error_description'] ?? $token['error']], 400);
        }

        file_put_contents(storage_path('app/drive_token.json'), json_encode($token));

        return redirect()->route('secretary.index')->with('success', '✅ Google Drive connected successfully!');
    }
}