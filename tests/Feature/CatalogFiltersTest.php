<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Category;
use App\Models\Collection as BookCollection;
use App\Models\Product;
use App\Models\Publisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

/**
 * Búsqueda, filtros y orden de /libros.
 *
 * Todo se comprueba sobre la respuesta del servidor: si algo de esto pasara a
 * resolverse en el navegador, estos tests dejarían de pasar.
 */
class CatalogFiltersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Títulos de la página, en el orden en que llegan.
     *
     * @param  array<string, mixed>  $query
     * @return array<int, string>
     */
    private function titles(array $query = []): array
    {
        $products = $this->get(route('books.index', $query))
            ->assertOk()
            ->viewData('page')['props']['products'];

        return array_column($products['data'], 'title');
    }

    /*
    |--------------------------------------------------------------------------
    | Listado
    |--------------------------------------------------------------------------
    */

    public function test_the_listing_returns_the_active_catalogue_with_its_filter_options(): void
    {
        Product::factory()->count(3)->create();
        Product::factory()->inactive()->create();

        $this->get(route('books.index'))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->component('Catalog/Index')
                    ->has('products.data', 3)
                    ->where('products.total', 3)
                    ->has('options.categorias')
                    ->has('options.autores')
                    ->has('options.editoriales')
                    ->has('options.colecciones')
                    ->has('options.disponibilidad')
                    ->has('options.precio')
                    ->has('options.orden', 5)
                    ->where('filters.orden', 'relevancia')
                    ->where('chips', [])
            );
    }

    public function test_the_pending_taxonomies_travel_declared_but_empty(): void
    {
        Product::factory()->create();

        $this->get(route('books.index'))->assertInertia(
            fn (AssertableInertia $page) => $page
                ->where('options.pendientes.0.value', 'edad')
                ->where('options.pendientes.1.value', 'sacramento')
                ->where('options.pendientes.2.value', 'tematica')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Búsqueda
    |--------------------------------------------------------------------------
    */

    public function test_it_searches_by_title(): void
    {
        Product::factory()->create(['title' => 'Cartas desde Patmos', 'slug' => 'cartas-desde-patmos']);
        Product::factory()->create(['title' => 'Salmos para el camino', 'slug' => 'salmos-para-el-camino']);

        $this->assertSame(['Cartas desde Patmos'], $this->titles(['q' => 'Patmos']));
    }

    public function test_it_searches_by_author(): void
    {
        $author = Author::factory()->create(['name' => 'Aurelia Valdovinos', 'slug' => 'aurelia-valdovinos']);

        $mine = Product::factory()->create(['title' => 'Umbrales', 'slug' => 'umbrales-busqueda']);
        $mine->authors()->attach($author);

        Product::factory()->create(['title' => 'Otro título', 'slug' => 'otro-titulo-busqueda']);

        $this->assertSame(['Umbrales'], $this->titles(['q' => 'Valdovinos']));
    }

    public function test_it_searches_by_isbn_with_and_without_separators(): void
    {
        Product::factory()->create([
            'title' => 'Con ISBN',
            'slug' => 'con-isbn',
            'isbn' => '978-956-99-0042-7',
        ]);
        Product::factory()->create(['title' => 'Sin ISBN', 'slug' => 'sin-isbn', 'isbn' => null]);

        $this->assertSame(['Con ISBN'], $this->titles(['q' => '978-956-99-0042-7']));
        $this->assertSame(['Con ISBN'], $this->titles(['q' => '9789569900427']));
    }

    public function test_it_searches_by_description(): void
    {
        Product::factory()->create([
            'title' => 'Sin pistas en el título',
            'slug' => 'sin-pistas',
            'description' => 'Un comentario del profeta Nabucodonosor y su tiempo.',
        ]);
        Product::factory()->create(['title' => 'Ajeno', 'slug' => 'ajeno-descripcion', 'description' => 'Nada.']);

        $this->assertSame(['Sin pistas en el título'], $this->titles(['q' => 'Nabucodonosor']));
    }

    public function test_it_searches_by_collection(): void
    {
        $collection = BookCollection::factory()->create(['name' => 'Vísperas', 'slug' => 'visperas']);

        Product::factory()->for($collection)->create(['title' => 'De la colección', 'slug' => 'de-la-coleccion']);
        Product::factory()->create(['title' => 'De otra', 'slug' => 'de-otra-coleccion']);

        $this->assertSame(['De la colección'], $this->titles(['q' => 'Vísperas']));
    }

    public function test_every_word_of_the_search_must_match(): void
    {
        Product::factory()->create(['title' => 'Cartas del Adviento', 'slug' => 'cartas-del-adviento']);
        Product::factory()->create(['title' => 'Cartas de Pascua', 'slug' => 'cartas-de-pascua']);

        $this->assertSame(['Cartas del Adviento'], $this->titles(['q' => 'Cartas Adviento']));
    }

    public function test_a_search_without_results_offers_sections_to_continue(): void
    {
        Product::factory()->count(2)->create();

        $this->get(route('books.index', ['q' => 'zzzzz']))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->where('products.total', 0)
                    ->has('products.data', 0)
                    ->has('suggestions')
                    ->where('chips.0.key', 'q')
                    ->where('chips.0.value', 'zzzzz')
            );

        $suggestions = $this->get(route('books.index', ['q' => 'zzzzz']))
            ->viewData('page')['props']['suggestions'];

        $this->assertNotEmpty($suggestions);
        $this->assertStringContainsString('categoria=', $suggestions[0]['href']);
    }

    /*
    |--------------------------------------------------------------------------
    | Filtros
    |--------------------------------------------------------------------------
    */

    public function test_it_filters_by_category_including_its_subsections(): void
    {
        $section = Category::factory()->create(['name' => 'Biblias', 'slug' => 'biblias']);
        $subsection = Category::factory()->childOf($section)->create();

        Product::factory()->for($section)->create(['title' => 'De la sección', 'slug' => 'de-la-seccion']);
        Product::factory()->for($subsection)->create(['title' => 'De la subsección', 'slug' => 'de-la-subseccion']);
        Product::factory()->create(['title' => 'De otra parte', 'slug' => 'de-otra-parte']);

        $titles = $this->titles(['categoria' => 'biblias', 'orden' => 'az']);

        $this->assertSame(['De la sección', 'De la subsección'], $titles);
    }

    public function test_it_filters_by_author(): void
    {
        $author = Author::factory()->create(['name' => 'Ismael Quiroga', 'slug' => 'ismael-quiroga']);

        $mine = Product::factory()->create(['title' => 'Suyo', 'slug' => 'suyo']);
        $mine->authors()->attach($author);

        Product::factory()->create(['title' => 'Ajeno', 'slug' => 'ajeno-autor']);

        $this->assertSame(['Suyo'], $this->titles(['autor' => 'ismael-quiroga']));
    }

    public function test_it_filters_by_publisher(): void
    {
        $publisher = Publisher::factory()->create(['name' => 'Ediciones Surco', 'slug' => 'ediciones-surco']);

        Product::factory()->for($publisher)->create(['title' => 'Del sello', 'slug' => 'del-sello']);
        Product::factory()->create(['title' => 'De otro sello', 'slug' => 'de-otro-sello']);

        $this->assertSame(['Del sello'], $this->titles(['editorial' => 'ediciones-surco']));
    }

    public function test_it_filters_by_collection(): void
    {
        $collection = BookCollection::factory()->create(['name' => 'Lámpara', 'slug' => 'lampara']);

        Product::factory()->for($collection)->create(['title' => 'De la serie', 'slug' => 'de-la-serie']);
        Product::factory()->create(['title' => 'Fuera de serie', 'slug' => 'fuera-de-serie']);

        $this->assertSame(['De la serie'], $this->titles(['coleccion' => 'lampara']));
    }

    public function test_it_filters_by_availability(): void
    {
        Product::factory()->create(['title' => 'En stock', 'slug' => 'en-stock', 'stock' => 12]);
        Product::factory()->outOfStock()->create(['title' => 'Agotado', 'slug' => 'agotado']);

        $this->assertSame(['En stock'], $this->titles(['disponibilidad' => 'disponible']));
        $this->assertSame(['Agotado'], $this->titles(['disponibilidad' => 'bajo-pedido']));
    }

    public function test_it_filters_by_price_range(): void
    {
        Product::factory()->create(['title' => 'Barato', 'slug' => 'barato', 'price' => 3000]);
        Product::factory()->create(['title' => 'Medio', 'slug' => 'medio', 'price' => 12000]);
        Product::factory()->create(['title' => 'Caro', 'slug' => 'caro', 'price' => 40000]);

        $this->assertSame(['Medio'], $this->titles(['precio_min' => 5000, 'precio_max' => 20000]));
        $this->assertSame(['Caro'], $this->titles(['precio_min' => 20000]));
        $this->assertSame(['Barato'], $this->titles(['precio_max' => 5000]));
    }

    public function test_an_inverted_price_range_is_read_the_right_way_round(): void
    {
        Product::factory()->create(['title' => 'Medio', 'slug' => 'medio-invertido', 'price' => 12000]);
        Product::factory()->create(['title' => 'Caro', 'slug' => 'caro-invertido', 'price' => 40000]);

        $this->assertSame(['Medio'], $this->titles(['precio_min' => 20000, 'precio_max' => 5000]));
    }

    public function test_filters_combine(): void
    {
        $category = Category::factory()->create(['slug' => 'catequesis']);
        $author = Author::factory()->create(['slug' => 'marta-donoso']);

        $match = Product::factory()->for($category)->create([
            'title' => 'El que cumple todo',
            'slug' => 'el-que-cumple-todo',
            'price' => 9000,
            'stock' => 4,
        ]);
        $match->authors()->attach($author);

        // Misma sección y mismo autor, pero fuera del rango de precio.
        $tooExpensive = Product::factory()->for($category)->create([
            'title' => 'Demasiado caro',
            'slug' => 'demasiado-caro',
            'price' => 90000,
        ]);
        $tooExpensive->authors()->attach($author);

        $titles = $this->titles([
            'categoria' => 'catequesis',
            'autor' => 'marta-donoso',
            'disponibilidad' => 'disponible',
            'precio_max' => 10000,
        ]);

        $this->assertSame(['El que cumple todo'], $titles);
    }

    public function test_an_unknown_slug_is_ignored_instead_of_emptying_the_catalogue(): void
    {
        Product::factory()->count(2)->create();

        $this->get(route('books.index', ['categoria' => 'no-existe']))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->where('products.total', 2)
                    ->where('filters.categoria', null)
                    ->where('chips', [])
            );
    }

    public function test_the_active_filters_travel_with_a_readable_label(): void
    {
        Category::factory()->create(['name' => 'Liturgia', 'slug' => 'liturgia']);
        Product::factory()->create();

        $this->get(route('books.index', ['categoria' => 'liturgia']))
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->where('filters.categoria', 'liturgia')
                    ->where('chips.0.key', 'categoria')
                    ->where('chips.0.label', 'Sección')
                    ->where('chips.0.value', 'Liturgia')
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Opciones de la barra
    |--------------------------------------------------------------------------
    */

    public function test_the_options_only_offer_values_that_have_results(): void
    {
        $used = Publisher::factory()->create(['name' => 'Con títulos', 'slug' => 'con-titulos']);
        Publisher::factory()->create(['name' => 'Sin títulos', 'slug' => 'sin-titulos']);

        Product::factory()->for($used)->create();

        $publishers = $this->get(route('books.index'))
            ->viewData('page')['props']['options']['editoriales'];

        $this->assertSame(['con-titulos'], array_column($publishers, 'value'));
        $this->assertSame(1, $publishers[0]['count']);
    }

    public function test_each_facet_is_counted_without_its_own_filter(): void
    {
        $biblias = Category::factory()->create(['name' => 'Biblias', 'slug' => 'biblias']);
        $ninos = Category::factory()->create(['name' => 'Niños', 'slug' => 'ninos']);

        Product::factory()->count(2)->for($biblias)->create();
        Product::factory()->for($ninos)->create();

        // Estando en "Biblias", la barra debe seguir mostrando "Niños": si se
        // contara también el filtro propio, marcaría cero y no habría por dónde
        // salir de la sección.
        $categories = $this->get(route('books.index', ['categoria' => 'biblias']))
            ->viewData('page')['props']['options']['categorias'];

        $counts = array_combine(array_column($categories, 'value'), array_column($categories, 'count'));

        $this->assertSame(['biblias' => 2, 'ninos' => 1], $counts);
    }

    public function test_the_price_bounds_follow_the_other_filters(): void
    {
        $category = Category::factory()->create(['slug' => 'oracion']);

        Product::factory()->for($category)->create(['price' => 4000]);
        Product::factory()->for($category)->create(['price' => 18000]);
        Product::factory()->create(['price' => 99000]);

        $price = $this->get(route('books.index', ['categoria' => 'oracion']))
            ->viewData('page')['props']['options']['precio'];

        $this->assertSame(['min' => 4000, 'max' => 18000], $price);
    }

    /*
    |--------------------------------------------------------------------------
    | Orden
    |--------------------------------------------------------------------------
    */

    public function test_it_sorts_by_price_in_both_directions(): void
    {
        Product::factory()->create(['title' => 'Medio', 'slug' => 'orden-medio', 'price' => 12000]);
        Product::factory()->create(['title' => 'Barato', 'slug' => 'orden-barato', 'price' => 3000]);
        Product::factory()->create(['title' => 'Caro', 'slug' => 'orden-caro', 'price' => 40000]);

        $this->assertSame(['Barato', 'Medio', 'Caro'], $this->titles(['orden' => 'precio-asc']));
        $this->assertSame(['Caro', 'Medio', 'Barato'], $this->titles(['orden' => 'precio-desc']));
    }

    public function test_it_sorts_alphabetically(): void
    {
        Product::factory()->create(['title' => 'Cartas', 'slug' => 'az-cartas']);
        Product::factory()->create(['title' => 'Amanecer', 'slug' => 'az-amanecer']);
        Product::factory()->create(['title' => 'Bautismo', 'slug' => 'az-bautismo']);

        $this->assertSame(['Amanecer', 'Bautismo', 'Cartas'], $this->titles(['orden' => 'az']));
    }

    public function test_it_sorts_by_edition_date(): void
    {
        Product::factory()->create(['title' => 'Vieja', 'slug' => 'fecha-vieja', 'published_at' => '2015-01-01']);
        Product::factory()->create(['title' => 'Nueva', 'slug' => 'fecha-nueva', 'published_at' => '2025-06-01']);
        Product::factory()->create(['title' => 'Media', 'slug' => 'fecha-media', 'published_at' => '2020-03-01']);

        $this->assertSame(['Nueva', 'Media', 'Vieja'], $this->titles(['orden' => 'recientes']));
    }

    public function test_relevance_without_a_search_puts_the_featured_titles_first(): void
    {
        Product::factory()->create(['title' => 'Corriente', 'slug' => 'rel-corriente', 'published_at' => '2025-01-01']);
        Product::factory()->newRelease()->create([
            'title' => 'Novedad',
            'slug' => 'rel-novedad',
            'published_at' => '2024-01-01',
        ]);
        Product::factory()->featured()->create([
            'title' => 'Destacado',
            'slug' => 'rel-destacado',
            'published_at' => '2023-01-01',
        ]);

        $this->assertSame(['Destacado', 'Novedad', 'Corriente'], $this->titles());
    }

    public function test_relevance_with_a_search_puts_the_title_match_first(): void
    {
        Product::factory()->create([
            'title' => 'Un comentario cualquiera',
            'slug' => 'rel-descripcion',
            'description' => 'Trata sobre el Adviento con detalle.',
            'published_at' => '2025-06-01',
        ]);
        Product::factory()->create([
            'title' => 'Adviento',
            'slug' => 'rel-titulo-exacto',
            'published_at' => '2010-01-01',
        ]);

        $this->assertSame(['Adviento', 'Un comentario cualquiera'], $this->titles(['q' => 'Adviento']));
    }

    public function test_an_unknown_sort_falls_back_to_relevance(): void
    {
        Product::factory()->create();

        $this->get(route('books.index', ['orden' => 'por-color']))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('filters.orden', 'relevancia'));
    }

    /*
    |--------------------------------------------------------------------------
    | Paginación
    |--------------------------------------------------------------------------
    */

    public function test_pagination_keeps_the_filters_in_the_links(): void
    {
        $perPage = (int) config('paulinas.catalog.per_page');
        $category = Category::factory()->create(['slug' => 'formacion']);

        Product::factory()->count($perPage + 2)->for($category)->create();
        Product::factory()->count(3)->create();

        $response = $this->get(route('books.index', ['categoria' => 'formacion', 'orden' => 'az']));

        $products = $response->viewData('page')['props']['products'];

        $this->assertSame($perPage + 2, $products['total']);
        $this->assertCount($perPage, $products['data']);
        $this->assertStringContainsString('categoria=formacion', $products['links'][1]['url']);
        $this->assertStringContainsString('orden=az', $products['links'][1]['url']);

        $this->get(route('books.index', ['categoria' => 'formacion', 'orden' => 'az', 'page' => 2]))
            ->assertInertia(fn (AssertableInertia $page) => $page->has('products.data', 2));
    }

    public function test_the_two_pages_of_a_sorted_listing_do_not_overlap(): void
    {
        $perPage = (int) config('paulinas.catalog.per_page');

        // Todos con el mismo precio: sin un desempate estable, el motor podría
        // repetir un título en las dos páginas.
        Product::factory()->count($perPage + 5)->create(['price' => 9990]);

        $first = $this->titles(['orden' => 'precio-asc']);
        $second = $this->titles(['orden' => 'precio-asc', 'page' => 2]);

        $this->assertCount($perPage, $first);
        $this->assertCount(5, $second);
        $this->assertEmpty(array_intersect($first, $second));
    }
}
