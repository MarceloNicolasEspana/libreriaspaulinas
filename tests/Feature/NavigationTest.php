<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    // La portada consulta el catálogo: basta con que existan las tablas.
    use RefreshDatabase;

    public function test_it_shares_the_navigation_structure_with_every_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->has('navigation.primary', 8)
                    ->has('navigation.footer', 3)
                    ->where('navigation.primary.0.label', 'Libros')
                    ->where('navigation.footer.0.heading', 'Librería')
            );
    }

    /**
     * Cada enlace anunciado en la navegación debe resolver, para que el header y
     * el footer nunca apunten a un 404.
     *
     * @return array<int, array{string}>
     */
    public static function navigationHrefProvider(): array
    {
        $config = require __DIR__.'/../../config/navigation.php';

        $hrefs = collect($config['primary'])
            ->concat(collect($config['footer'])->flatMap(fn (array $column) => $column['links']))
            ->pluck('href')
            ->unique();

        return $hrefs->map(fn (string $href) => [$href])->values()->all();
    }

    #[DataProvider('navigationHrefProvider')]
    public function test_every_navigation_link_resolves(string $href): void
    {
        $this->get($href)->assertOk();
    }

    public function test_unknown_urls_still_return_not_found(): void
    {
        $this->get('/una-ruta-que-no-existe')->assertNotFound();
    }
}
