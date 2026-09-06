<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $content = implode("\n", [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /carrito',
            'Sitemap: '.route('sitemap'),
            '',
        ]);

        return response($content, headers: ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
