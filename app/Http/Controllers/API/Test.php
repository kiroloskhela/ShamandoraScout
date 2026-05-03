<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Test extends Controller
{
    /**
     * Handle POST request and return JSON response
     */
    public function store(Request $request)
    {
        return response()->json([
            'body' => [
                'Data' => [
                    [
                        'label' => 'Kiro',
                        'code' => 'Kiro'
                    ],
                    [
                        'label' => 'Abbas',
                        'code' => 'Abbas'
                    ]
                ]
            ]
        ]);
    }

    /**
     * Alternative method that echoes back the input
     */
    public function storeWithInput(Request $request)
    {
        return response()->json([
            'body' => [
                'Data' => $request->all()
            ]
        ]);
    }
}