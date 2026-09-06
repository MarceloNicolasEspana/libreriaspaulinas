<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Models\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Metadatos de las páginas públicas.
 *
 * El layout público emite title, description, canonical, Open Graph, Twitter y
 * los datos estructurados a partir de una sola prop "seo". Estos casos
 * comprueban que cada página la entrega completa: si un controlador nuevo la
 * olvida, la página se publicaría sin metadatos y nada más lo advertiría.
 */
class SeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function publicPageProvider(): array
    {
        return [
            'portada' => ['/'],
            'catalogo' => ['/libros'],
            'catalogo filtrado' => ['/libros?categoria=biblias'],
            'busqueda' => ['/libros?q=biblia'],
            'librerias' => ['/librerias'],
            'recursos' => ['/recursos'],
            'novedades' => ['/novedades'],
            'quienes somos' => ['/quienes-somos'],
            'contacto' => ['/contacto'],
            'carrito' => ['/carrito'],
        ];
    }

    #[DataProvider('publicPageProvider')]
    public function test_every_public_page_carries_a_complete_seo_payload(string $url): void
    {
        $this->assertSeoPayload($this->get($url), $url);
    }

    public function test_the_detail_pages_carry_a_complete_seo_payload(): void
    {
        $urls = [
            route('books.show', Product::query()->active()->firstOrFail(), absolute: false),
            route('categories.show', Category::query()->active()->firstOrFail(), absolute: false),
            route('branches.show', Branch::query()->active()->firstOrFail(), absolute: false),
            route('posts.show', Post::query()->published()->firstOrFail(), absolute: false),
            route('resources.show', Resource::query()->published()->firstOrFail(), absolute: false),
        ];

        foreach ($urls as $url) {
            $this->assertSeoPayload($this->get($url), $url);
        }
    }

    public function test_the_canonical_points_at_the_page_itself_without_the_query_string(): void
    {
        $seo = $this->seoOf($this->get('/libros?categoria=biblias&page=2'));

        $this->assertSame(url('/libros'), $seo['canonical']);
    }

    /**
     * Las páginas que no aportan nada a un buscador no deben indexarse: los
     * resultados de búsqueda son infinitos y el carrito es privado.
     */
    public function test_search_results_and_the_cart_are_not_indexable(): void
    {
        $this->assertTrue($this->seoOf($this->get('/libros?q=biblia'))['noindex']);
        $this->assertTrue($this->seoOf($this->get('/carrito'))['noindex']);
        $this->assertFalse($this->seoOf($this->get('/libros'))['noindex']);
    }

    public function test_the_home_page_describes_the_organization(): void
    {
        $this->assertContains('Organization', $this->schemaTypesOf($this->get('/')));
    }

    public function test_a_product_page_describes_the_product_the_book_and_the_trail(): void
    {
        $product = Product::query()->active()->whereNotNull('isbn')->firstOrFail();

        $response = $this->get(route('books.show', $product, absolute: false));
        $types = $this->schemaTypesOf($response);

        $this->assertContains('Product', $types);
        $this->assertContains('Book', $types);
        $this->assertContains('BreadcrumbList', $types);

        $schemas = collect($this->seoOf($response)['schemas']);
        $offer = $schemas->firstWhere('@type', 'Product')['offers'];

        $this->assertSame(route('books.show', $product), $offer['url']);
        $this->assertSame(config('paulinas.currency.code'), $offer['priceCurrency']);
        $this->assertSame(
            $product->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            $offer['availability'],
        );
        $this->assertSame($product->isbn, $schemas->firstWhere('@type', 'Book')['isbn']);
    }

    /**
     * Un título sin ISBN no puede publicar un schema Book: el ISBN es lo que lo
     * identifica. La ficha se sigue describiendo como Product.
     */
    public function test_a_product_without_an_isbn_omits_the_book_schema(): void
    {
        $product = Product::query()->active()->firstOrFail();
        $product->update(['isbn' => null]);

        $types = $this->schemaTypesOf($this->get(route('books.show', $product, absolute: false)));

        $this->assertContains('Product', $types);
        $this->assertNotContains('Book', $types);
    }

    public function test_a_branch_page_describes_the_bookstore(): void
    {
        $branch = Branch::query()->active()->firstOrFail();

        $response = $this->get(route('branches.show', $branch, absolute: false));

        $store = collect($this->seoOf($response)['schemas'])->firstWhere('@type', 'BookStore');

        $this->assertNotNull($store);
        $this->assertSame($branch->name, $store['name']);
        $this->assertSame($branch->address, $store['address']['streetAddress']);
    }

    public function test_a_post_page_describes_the_article(): void
    {
        $post = Post::query()->published()->firstOrFail();

        $response = $this->get(route('posts.show', $post, absolute: false));
        $article = collect($this->seoOf($response)['schemas'])->firstWhere('@type', 'Article');

        $this->assertNotNull($article);
        $this->assertSame($post->title, $article['headline']);
    }

    /**
     * Las migas alimentan a la vez la navegación visible y el BreadcrumbList,
     * así que deben empezar en la portada y terminar en la página actual.
     */
    public function test_the_breadcrumb_trail_runs_from_the_home_page_to_the_current_page(): void
    {
        $product = Product::query()->active()->firstOrFail();
        $url = route('books.show', $product, absolute: false);

        $breadcrumbs = $this->seoOf($this->get($url))['breadcrumbs'];

        $this->assertSame('Inicio', $breadcrumbs[0]['name']);
        $this->assertSame('/', $breadcrumbs[0]['href']);
        $this->assertSame($product->title, end($breadcrumbs)['name']);
        $this->assertSame($url, end($breadcrumbs)['href']);
    }

    public function test_the_sitemap_lists_the_published_pages_and_leaves_out_the_rest(): void
    {
        $product = Product::query()->active()->firstOrFail();
        $hidden = Product::query()->active()->whereKeyNot($product->getKey())->firstOrFail();
        $hidden->update(['active' => false]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee(route('home'), escape: false);
        $response->assertSee(route('books.show', $product), escape: false);
        $response->assertDontSee(route('books.show', $hidden), escape: false);
        $response->assertDontSee(route('cart.index'), escape: false);
    }

    public function test_robots_points_at_the_sitemap_and_closes_the_private_sections(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertSee('Sitemap: '.route('sitemap'), escape: false);
        $response->assertSee('Disallow: /admin', escape: false);
        $response->assertSee('Disallow: /carrito', escape: false);
    }

    /**
     * El JSON-LD tiene que estar en el documento que recibe el rastreador, no
     * solo en las props de la página.
     */
    public function test_the_structured_data_reaches_the_rendered_document(): void
    {
        $product = Product::query()->active()->whereNotNull('isbn')->firstOrFail();

        $response = $this->get(route('books.show', $product, absolute: false));
        $rendered = $this->renderedSchemas($response);

        $this->assertSame(
            $this->schemaTypesOf($response),
            array_column($rendered, '@type'),
            'El documento no publica los mismos datos estructurados que la página declara.',
        );

        $this->assertSame($product->title, collect($rendered)->firstWhere('@type', 'Product')['name']);
    }

    public function test_the_home_page_publishes_the_organization_in_the_document(): void
    {
        $organization = collect($this->renderedSchemas($this->get('/')))->firstWhere('@type', 'Organization');

        $this->assertNotNull($organization);
        $this->assertSame(config('paulinas.legal_name'), $organization['name']);
    }

    /**
     * Un título del catálogo es contenido editable: si se colara sin escapar,
     * cerraría la etiqueta y el resto se ejecutaría como JavaScript.
     */
    public function test_a_title_cannot_break_out_of_the_structured_data_block(): void
    {
        $product = Product::query()->active()->firstOrFail();
        $product->update(['title' => 'Biblia </script><script>alert(1)</script>']);

        $response = $this->get(route('books.show', $product, absolute: false));

        $this->assertStringNotContainsString('<script>alert(1)</script>', $response->getContent());
        $this->assertSame(
            $product->title,
            collect($this->renderedSchemas($response))->firstWhere('@type', 'Product')['name'],
        );
    }

    /** @return array<string, mixed> */
    private function seoOf(TestResponse $response): array
    {
        $response->assertOk();

        return $response->viewData('page')['props']['seo'];
    }

    /** @return array<int, string> */
    private function schemaTypesOf(TestResponse $response): array
    {
        return collect($this->seoOf($response)['schemas'])->pluck('@type')->all();
    }

    /**
     * Los datos estructurados que el documento publica de verdad.
     *
     * Se leen del HTML y no de la prop: el compilador de Vue descarta las
     * etiquetas <script> de una plantilla, así que un JSON-LD montado desde un
     * componente desaparecería sin que la prop lo delatara.
     *
     * @return array<int, array<string, mixed>>
     */
    private function renderedSchemas(TestResponse $response): array
    {
        preg_match_all(
            '#<script type="application/ld\+json">(.*?)</script>#s',
            $response->getContent(),
            $matches,
        );

        return array_map(
            fn (string $json): array => json_decode(html_entity_decode($json, ENT_QUOTES), associative: true),
            $matches[1],
        );
    }

    private function assertSeoPayload(TestResponse $response, string $url): void
    {
        $seo = $this->seoOf($response);

        foreach (['title', 'description', 'canonical', 'image', 'type', 'noindex', 'breadcrumbs', 'schemas'] as $key) {
            $this->assertArrayHasKey($key, $seo, "Falta \"{$key}\" en el SEO de {$url}.");
        }

        $this->assertNotEmpty($seo['title'], "Falta el título de {$url}.");
        $this->assertNotEmpty($seo['description'], "Falta la descripción de {$url}.");
        $this->assertStringStartsWith('http', $seo['canonical'], "El canonical de {$url} no es absoluto.");
        $this->assertStringStartsWith('http', $seo['image'], "La imagen social de {$url} no es absoluta.");
        $this->assertIsBool($seo['noindex']);
    }
}
