<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Institutional/About', [
            'seo' => [
                'title' => 'Quiénes somos',
                'description' => 'Conoce a las Hijas de San Pablo, su historia y su misión de evangelizar en la cultura de la comunicación.',
            ],
            'sources' => [
                [
                    'label' => 'Hijas de San Pablo — Quiénes somos',
                    'href' => 'https://www.paoline.org/quienes-somos/?lang=es',
                ],
                [
                    'label' => 'Hijas de San Pablo — Misión paulina',
                    'href' => 'https://www.paoline.org/quienes-somos/missione-paolina/?lang=es',
                ],
                [
                    'label' => 'Paulinas Chile — presencia y misión',
                    'href' => 'https://www.paulinas.cl/link_vocacional.pdf',
                ],
            ],
        ]);
    }
}
