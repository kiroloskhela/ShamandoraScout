<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
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
            'version'  => 'required|string',
        ]);

        $platform        = $request->query('platform');
        $clientVersion   = $request->query('version');
        $config          = config('app_version');
        $platform_config = $config[$platform];

        $needsUpdate = version_compare($clientVersion, $platform_config['latest_version'], '<');
        $forceUpdate = $platform_config['force_update'] || version_compare($clientVersion, $platform_config['min_version'], '<');

        return response()->json([
            'success' => true,
            'data'    => [
                $platform => [
                    'latest_version' => $platform_config['latest_version'],
                    'min_version'    => $platform_config['min_version'],
                    'force_update'   => $forceUpdate,
                    'url'            => $platform_config['url'],
                ],
                'maintenance'  => $config['maintenance'],
                'update_ui'    => $config['update_ui'],
                'needs_update' => $needsUpdate,
                'force_update' => $forceUpdate,
            ],
        ]);
    }
}