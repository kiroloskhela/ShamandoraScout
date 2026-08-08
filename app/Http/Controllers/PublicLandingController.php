<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PublicLandingController extends Controller
{
    public function __invoke(): View
    {
        return view('landing');
    }
}
