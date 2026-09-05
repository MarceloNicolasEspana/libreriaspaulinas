<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\DemoContent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Página transitoria para las secciones ya presentes en la navegación pero aún
 * sin contenido propio.
 *
 * Existe para que los enlaces del header y del footer sean reales y navegables
 * durante el desarrollo del sistema visual. Cada sección irá reemplazando esta
 * ruta por su propio controlador; cuando no quede ninguna, este archivo se
 * elimina junto con su ruta comodín.
 */
class SectionPlaceholderController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $title = $this->titleFor($request->path());

        return Inertia::render('SectionPlaceholder', [
            'seo' => [
                'title' => $title,
                'description' => "Sección {$title} de Librerías Paulinas Chile.",
            ],
            'section' => $title,
        ]);
    }

    /**
     * Reconstruye un título legible a partir del último segmento de la ruta.
     */
    private function titleFor(string $path): string
    {
        $labels = collect(config('navigation.primary'))
            ->concat(collect(config('navigation.footer'))->flatMap(fn (array $column) => $column['links']))
            ->mapWithKeys(fn (array $item) => [ltrim($item['href'], '/') => $item['label']])
            ->merge(
                collect(DemoContent::placeholderLinks())
                    ->mapWithKeys(fn (string $label, string $href) => [ltrim($href, '/') => $label])
            );

        return $labels->get($path, str(str($path)->afterLast('/'))->replace('-', ' ')->title()->value());
    }
}
