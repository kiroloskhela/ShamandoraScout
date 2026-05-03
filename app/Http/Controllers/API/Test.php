<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

// class Test extends Controller
// {
//     /**
//      * Handle POST request and return JSON response
//      */
//     public function store(Request $request)
//     {
//         return response()->json([
//             'header' => [
//                 'serverDate' => now()->format('Y-m-d\TH:i:s.uP'),
//                 'responseLanguage' => 'Ar',
//                 'deviceType' => null,
//                 'serialNumber' => '61adfd77-815b-4c9e-839b-4719a10a4143',
//                 'appVersion' => '4.0.1',
//                 'identifiers' => null,
//                 'preferedLanguage' => 'Ar',
//                 'clientDate' => $request->input('clientDate', '2024-12-03T15:25:08'),
//                 'requestUid' => (string) Str::uuid(),
//                 'messageCode' => 'createConsumerLoan',
//             ],

//             'body' => [
//                 'data' => [
//                     [
//                         'label' => 'Kiro',
//                         'code' => 'Kiro',
//                     ],
//                     [
//                         'label' => 'Arsany',
//                         'code' => 'Arsany',
//                     ],
//                     [
//                         'label' => 'Done',
//                         'code' => 'Done',
//                     ],
//                     [
//                         'label' => 'Initiated',
//                         'code' => 'Initiated',
//                     ],
//                     [
//                         'label' => 'Pipeline',
//                         'code' => 'Pipeline',
//                     ],
//                     [
//                         'label' => 'Rejected',
//                         'code' => 'Rejected',
//                     ],
//                 ],
//                 'code' => 200,
//                 'message' => 'تمت العمليه بنجاح',
//             ],

//             'footer' => [
//                 'securityInfo' => null,
//             ],
//         ]);
//     }

//     /**
//      * Alternative method that echoes back the input
//      */
//     public function storeWithInput(Request $request)
//     {
//         return response()->json([
//             'header' => [
//                 'serverDate' => now()->format('Y-m-d\TH:i:s.uP'),
//                 'responseLanguage' => 'Ar',
//                 'deviceType' => null,
//                 'serialNumber' => '61adfd77-815b-4c9e-839b-4719a10a4143',
//                 'appVersion' => '4.0.1',
//                 'identifiers' => null,
//                 'preferedLanguage' => 'Ar',
//                 'clientDate' => $request->input('clientDate', now()->format('Y-m-d\TH:i:s')),
//                 'requestUid' => (string) Str::uuid(),
//                 'messageCode' => 'createConsumerLoan',
//             ],

//             'body' => [
//                 'data' => $request->all(),
//                 'code' => 200,
//                 'message' => 'تمت العمليه بنجاح',
//             ],

//             'footer' => [
//                 'securityInfo' => null,
//             ],
//         ]);
//     }
// } 






class Test extends Controller
{
    public function store(Request $request)
    {
        $header = $request->input('header', []);

        return response()->json([
            'header' => [
                'serverDate' => now()->format('Y-m-d\TH:i:s.uP'),
                'responseLanguage' => $header['preferedLanguage'] ?? 'Ar',
                'deviceType' => null,
                'serialNumber' => $header['serialNumber'] ?? (string) Str::uuid(),
                'appVersion' => $header['appVersion'] ?? '4.0.1',
                'identifiers' => null,
                'preferedLanguage' => $header['preferedLanguage'] ?? 'Ar',
                'clientDate' => $header['clientDate'] ?? null,
                'requestUid' => $header['requestUid'] ?? (string) Str::uuid(),
                'messageCode' => $header['messageCode'] ?? 'createConsumerLoan',
            ],

            'body' => [
                'data' => [
                    [
                        'label' => 'Kiroloskhela',
                        'code' => 'Kiroloskhela',
                    ],
                    [
                        'label' => 'Arsany',
                        'code' => 'Arsany',
                    ],
                ],
                'code' => 200,
                'message' => 'تمت العمليه بنجاح',
            ],

            'footer' => [
                'securityInfo' => null,
            ],
        ]);
    }
}