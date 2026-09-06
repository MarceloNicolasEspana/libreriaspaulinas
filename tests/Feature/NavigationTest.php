<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Los enlaces del menú apuntan a secciones y librerías reales, así que el
     * caso parte del catálogo completo. Se siembra aquí y no con $seed: con
     * SQLite en memoria ese atributo solo tiene efecto en la primera clase del
     * proceso.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

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

    /**
     * Un enlace del menú puede responder 200 o, si la sección se movió, una
     * redirección permanente a su nueva dirección; lo que no puede es 404.
     */
    #[DataProvider('navigationHrefProvider')]
    public function test_every_navigation_link_resolves(string $href): void
    {
        $response = $this->get($href);

        if ($response->isRedirect()) {
            $this->assertSame(301, $response->getStatusCode(), "{$href} redirige sin ser permanente.");
            $this->followRedirects($response)->assertOk();

            return;
        }

        $response->assertOk("El enlace {$href} del menú no resuelve.");
    }

    public function test_unknown_urls_still_return_not_found(): void
    {
        $this->get('/una-ruta-que-no-existe')->assertNotFound();
    }
}
