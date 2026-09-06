<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Support\DemoContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * La portada muestra el catálogo, así que cada caso parte del catálogo de
     * demostración completo. Se siembra aquí y no con $seed: con SQLite en
     * memoria, ese atributo solo tiene efecto en la primera clase del proceso.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_home_page_renders_the_inertia_home_component(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->component('Home')
                    ->has('seo.title')
                    ->has('seo.description')
            );
    }

    public function test_home_page_shares_institutional_data(): void
    {
        $this->get(route('home'))
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->where('institution.shortName', config('paulinas.short_name'))
                    ->where('institution.legalName', config('paulinas.legal_name'))
                    ->where('currency.code', 'CLP')
            );
    }

    public function test_home_page_delivers_every_section_of_content(): void
    {
        $this->get(route('home'))
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->has('heroBanners')
                    // Se piden entre 4 y 8 novedades; la portada muestra el máximo.
                    ->has('newReleases', 8)
                    ->has('categories', 12)
                    ->has('featured', 4)
                    ->has('resources', 6)
                    ->has('branches', 3)
            );
    }

    public function test_the_hero_carries_a_single_active_campaign(): void
    {
        $active = collect(DemoContent::heroBanners())->where('active', true);

        $this->assertCount(1, $active, 'Se espera una sola campaña activa en el hero.');
    }

    public function test_each_product_card_has_the_data_it_needs(): void
    {
        $this->get(route('home'))
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->has(
                        'newReleases.0',
                        fn (AssertableInertia $product) => $product
                            ->has('id')
                            ->has('title')
                            ->has('author')
                            ->has('category')
                            ->has('categoryHref')
                            ->has('cover')
                            ->has('href')
                            ->whereType('price', 'integer')
                            ->has('availability')
                    )
            );
    }

    public function test_availability_is_always_a_state_the_card_can_render(): void
    {
        $response = $this->get(route('home'));

        $cards = [
            ...$response->viewData('page')['props']['newReleases'],
            ...$response->viewData('page')['props']['featured'],
        ];

        $this->assertNotEmpty($cards);

        foreach ($cards as $card) {
            $this->assertContains(
                $card['availability'],
                ['in_stock', 'low_stock', 'out_of_stock'],
                "Estado desconocido en \"{$card['title']}\"."
            );
        }
    }

    /**
     * Un título en preparación no puede colarse en la portada.
     */
    public function test_the_home_page_only_shows_active_products(): void
    {
        Product::query()->update(['active' => false, 'featured' => true, 'new_release' => true]);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->has('newReleases', 0)
                    ->has('featured', 0)
            );
    }

    /**
     * Las tarjetas de sección enlazan a la página real de la categoría.
     */
    public function test_category_cards_link_to_the_catalogue(): void
    {
        $response = $this->get(route('home'));

        foreach ($response->viewData('page')['props']['categories'] as $category) {
            $this->assertStringStartsWith('/categorias/', $category['href']);
            $this->assertIsInt($category['count']);
        }
    }

    public function test_each_branch_card_has_the_data_it_needs(): void
    {
        $response = $this->get(route('home'));

        foreach ($response->viewData('page')['props']['branches'] as $branch) {
            foreach (['name', 'commune', 'address', 'hours', 'href'] as $field) {
                $this->assertNotEmpty($branch[$field] ?? null, "Falta \"{$field}\" en una tarjeta de librería.");
            }

            // El teléfono y el WhatsApp son opcionales: no toda librería los
            // publica. La tarjeta debe recibir la clave igual, para decidir si
            // muestra el enlace de contacto.
            foreach (['phone', 'whatsapp', 'phoneUrl', 'whatsappUrl'] as $field) {
                $this->assertArrayHasKey($field, $branch);
            }
        }
    }

    /**
     * Ningún enlace interno de la portada puede terminar en un 404.
     *
     * Se recorre la portada ya renderizada en vez de una lista fija: así el
     * caso cubre las campañas, los accesos a recursos, las secciones, las
     * tarjetas de producto y las librerías con los datos que se publican.
     */
    public function test_every_link_shown_on_the_home_page_resolves(): void
    {
        $props = $this->get(route('home'))->viewData('page')['props'];

        $hrefs = collect($props['heroBanners'])->where('active', true)->pluck('buttonHref')
            ->concat(collect($props['resources'])->pluck('href'))
            ->concat(collect($props['categories'])->pluck('href'))
            ->concat(collect($props['branches'])->pluck('href'))
            ->concat(collect($props['newReleases'])->pluck('href'))
            ->concat(collect($props['featured'])->pluck('href'))
            ->filter()
            ->unique();

        $this->assertNotEmpty($hrefs);

        foreach ($hrefs as $href) {
            $this->get($href)->assertOk("El enlace {$href} de la portada no resuelve.");
        }
    }
}
