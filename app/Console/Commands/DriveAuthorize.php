<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client;

class DriveAuthorize extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'drive:authorize';

    /**
     * The console command description.
     */
    protected $description = 'Authorize Google Drive and save refresh token for backend uploads.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $credentials = storage_path('app/google-drive-credentials.json');
        $tokenFile   = storage_path('app/drive_token.json');

        if (!file_exists($credentials)) {
            $this->error("❌ Missing file: {$credentials}");
            $this->line('Place your OAuth client JSON file there (type: Web Application).');
            return self::FAILURE;
        }

        $client = new Client();
        $client->setAuthConfig($credentials);
        $client->setRedirectUri('https://shamandorascout.com/auth/google/callback');
        $client->setScopes([
            'https://www.googleapis.com/auth/drive',
            'https://www.googleapis.com/auth/drive.file',
            'https://www.googleapis.com/auth/drive.metadata'
        ]);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        $authUrl = $client->createAuthUrl();
        $this->info('🌐 Open this link in your browser and authorize access:');
        $this->line($authUrl);

        $code = $this->ask('Paste the "code=" value from the redirected URL here');

        if (empty($code)) {
            $this->error('❌ No code entered.');
            return self::FAILURE;
        }

        $token = $client->fetchAccessTokenWithAuthCode(trim($code));

        if (isset($token['error'])) {
            $this->error('❌ Error: ' . $token['error']);
            return self::FAILURE;
        }

        file_put_contents($tokenFile, json_encode($token));
        $this->info("✅ Token saved to {$tokenFile}");

        if (isset($token['refresh_token'])) {
            $this->info("🔁 Refresh token stored — backend uploads will now work automatically.");
        } else {
            $this->warn("⚠️ No refresh token received (maybe you previously approved this app). Revoke old access at https://myaccount.google.com/permissions and try again.");
        }

        return self::SUCCESS;
    }
}
