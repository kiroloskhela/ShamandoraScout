<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsTxtController extends Controller
{
    public function __invoke(): Response
    {
        $sitemap = rtrim((string) config('app.url'), '/').'/sitemap.xml';

        $body = "User-agent: *\nDisallow:\n\nSitemap: {$sitemap}\n";

        return response($body, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
