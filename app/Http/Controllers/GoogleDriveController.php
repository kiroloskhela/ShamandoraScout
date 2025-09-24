<?php

namespace App\Http\Controllers;

use Google\Client;
use Google\Service\Drive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GoogleDriveController extends Controller
{
    private function getClient(): Client
    {
        $client = new Client();
        $client->setAuthConfig(storage_path('app/google-drive-credentials.json'));
        $client->setRedirectUri(config('app.url') . '/auth/google/callback');
        $client->addScope(Drive::DRIVE_FILE); // ✅ safer than full DRIVE
        $client->setAccessType('offline');    // ✅ allows refresh_token
        $client->setPrompt('consent');        // ✅ ensures refresh_token returned
        return $client;
    }

    public function redirectToGoogle()
    {
        $client = $this->getClient();
        return redirect()->away($client->createAuthUrl());
    }

    public function handleGoogleCallback(Request $request)
    {
        $client = $this->getClient();

        if ($request->has('code')) {
            $token = $client->fetchAccessTokenWithAuthCode($request->input('code'));

            if (isset($token['error'])) {
                return response()->json(['error' => $token['error']], 400);
            }

            // ✅ Store token persistently
            Storage::put('google-token.json', json_encode($token));

            return response()->json(['status' => 'Google Drive connected successfully!']);
        }

        return "❌ Error: no code returned from Google.";
    }

    public function uploadTestFile()
    {
        $client = $this->getClient();

        // ✅ Load saved token
        if (Storage::exists('google-token.json')) {
            $token = json_decode(Storage::get('google-token.json'), true);
            $client->setAccessToken($token);

            // ✅ Refresh if expired
            if ($client->isAccessTokenExpired()) {
                if ($client->getRefreshToken()) {
                    $newToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                    $client->setAccessToken($newToken);
                    Storage::put('google-token.json', json_encode($newToken));
                }
            }
        } else {
            return "❌ No saved token, please connect first.";
        }

        $service = new Drive($client);

        // ✅ Upload test file
        $file = new Drive\DriveFile();
        $file->setName('test_upload.txt');

        $content = "Hello from Laravel at " . now();
        $createdFile = $service->files->create($file, [
            'data' => $content,
            'mimeType' => 'text/plain',
            'uploadType' => 'multipart',
        ]);

        return response()->json(['file_id' => $createdFile->id, 'name' => $createdFile->name]);
    }
}