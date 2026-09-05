<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Muestra la portada del sitio público.
     */
    public function __invoke(): Response
    {
        return Inertia::render('Home', [
            'seo' => [
                'title' => 'Inicio',
                'description' => 'Librerías Paulinas Chile: libros, material pastoral y recursos '
                    .'para la evangelización y la educación religiosa.',
            ],
        ]);
    }
}
