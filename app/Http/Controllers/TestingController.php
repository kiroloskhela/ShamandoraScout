<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

class TestingController extends Controller
{
    public function index()
    {
        return view('testing.index'); // will create this blade
    }

    public function upload(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:4096' // max 4MB
        ]);

        // Init Google client
        $client = new Client();
        $client->setAuthConfig(storage_path('app/google-drive-credentials.json'));

        // ⚠️ For testing: use session token (after OAuth login)
        $client->setAccessToken(session('google_drive_token'));

        // Create Drive service
        $service = new Drive($client);

        // File metadata
        $fileMetadata = new DriveFile([
            'name' => 'test_' . time() . '.' . $request->file('photo')->getClientOriginalExtension(),
        ]);

        // File content
        $content = file_get_contents($request->file('photo')->getRealPath());

        // Upload
        $file = $service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => $request->file('photo')->getMimeType(),
            'uploadType' => 'multipart',
            'fields' => 'id, webViewLink, webContentLink'
        ]);

        return back()->with('success', '✅ Uploaded to Drive: ' . $file->webViewLink);
    }
}