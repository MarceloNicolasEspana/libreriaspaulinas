<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

/**
 * Las cuatro URL del catálogo: /libros, /libros/{slug}, /categorias/{slug} y
 * /autores/{slug}.
 */
class CatalogPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_catalogue_lists_only_active_products(): void
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
            );
    }

    public function test_the_catalogue_paginates(): void
    {
        $perPage = (int) config('paulinas.catalog.per_page');

        Product::factory()->count($perPage + 3)->create();

        $this->get(route('books.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page->has('products.data', $perPage));

        $this->get(route('books.index', ['page' => 2]))
            ->assertInertia(fn (AssertableInertia $page) => $page->has('products.data', 3));
    }

    public function test_a_product_page_is_found_by_its_slug(): void
    {
        $product = Product::factory()->create();
        $product->authors()->attach(Author::factory()->create());

        $this->get(route('books.show', $product))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->component('Catalog/Show')
                    ->where('product.title', $product->title)
                    ->where('product.isbn', $product->isbn)
                    ->whereType('product.price', 'integer')
                    ->has('product.authors', 1)
                    ->has('product.category')
                    ->has('related')
            );
    }

    public function test_an_inactive_product_is_not_reachable(): void
    {
        $product = Product::factory()->inactive()->create();

        $this->get(route('books.show', $product))->assertNotFound();
    }

    public function test_an_unknown_product_slug_returns_not_found(): void
    {
        $this->get('/libros/un-titulo-que-no-existe')->assertNotFound();
    }

    public function test_related_titles_come_from_the_same_section_and_exclude_the_product_itself(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create();
        Product::factory()->count(2)->for($category)->create();
        Product::factory()->count(2)->create();

        $response = $this->get(route('books.show', $product));

        $related = $response->viewData('page')['props']['related'];

        $this->assertCount(2, $related);
        $this->assertNotContains($product->id, array_column($related, 'id'));
    }

    public function test_a_section_shows_its_own_titles_and_those_of_its_subsections(): void
    {
        $section = Category::factory()->create();
        $subsection = Category::factory()->childOf($section)->create();

        Product::factory()->count(2)->for($section)->create();
        Product::factory()->for($subsection)->create();
        Product::factory()->create();

        $this->get(route('categories.show', $section))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->component('Catalog/Category')
                    ->where('category.name', $section->name)
                    ->has('category.children', 1)
                    ->has('products.data', 3)
            );
    }

    public function test_an_inactive_section_is_not_reachable(): void
    {
        $category = Category::factory()->inactive()->create();

        $this->get(route('categories.show', $category))->assertNotFound();
    }

    public function test_an_author_page_lists_only_that_authors_active_titles(): void
    {
        $author = Author::factory()->create();

        $mine = Product::factory()->count(2)->create();
        $hidden = Product::factory()->inactive()->create();
        Product::factory()->create();

        $author->products()->attach($mine->push($hidden)->pluck('id'));

        $this->get(route('authors.show', $author))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->component('Catalog/Author')
                    ->where('author.name', $author->name)
                    ->has('products.data', 2)
            );
    }

    public function test_the_product_card_payload_is_the_same_everywhere(): void
    {
        Product::factory()->create();

        $card = $this->get(route('books.index'))->viewData('page')['props']['products']['data'][0];

        $this->assertSame(
            ['id', 'title', 'author', 'category', 'categoryHref', 'price', 'availability', 'cover', 'href'],
            array_keys($card),
        );
    }
}
