<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Support\AppSettings;
use Illuminate\Http\Request;

class VersionApiController extends Controller
{
    /**
     * GET /api/version/check?platform=android&version=1.3.0
     */
    public function check(Request $request)
    {
        $request->validate([
            'platform' => 'required|in:android,ios',
            'version' => 'required|string',
        ]);

        $platform = $request->query('platform');
        $clientVersion = $request->query('version');
        $config = AppSettings::appVersionConfig();
        $platformConfig = $config[$platform];

        $needsUpdate = version_compare($clientVersion, $platformConfig['latest_version'], '<');
        $forceUpdate = $platformConfig['force_update']
            || version_compare($clientVersion, $platformConfig['min_version'], '<');

        return response()->json([
            'success' => true,
            'data' => [
                $platform => [
                    'latest_version' => $platformConfig['latest_version'],
                    'min_version' => $platformConfig['min_version'],
                    'force_update' => $forceUpdate,
                    'url' => $platformConfig['url'],
                ],
                'maintenance' => $config['maintenance'],
                'update_ui' => $config['update_ui'],
                'needs_update' => $needsUpdate,
                'force_update' => $forceUpdate,
            ],
        ]);
    }
}
