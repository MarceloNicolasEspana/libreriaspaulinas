<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Publisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Relaciones, scopes y conversiones del catálogo.
 */
class CatalogModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_product_belongs_to_a_category_a_publisher_and_a_collection(): void
    {
        $category = Category::factory()->create();
        $publisher = Publisher::factory()->create();
        $collection = Collection::factory()->create();

        $product = Product::factory()
            ->for($category)
            ->for($publisher)
            ->for($collection)
            ->create();

        $this->assertTrue($product->category->is($category));
        $this->assertTrue($product->publisher->is($publisher));
        $this->assertTrue($product->collection->is($collection));

        $this->assertTrue($category->products->contains($product));
        $this->assertTrue($publisher->products->contains($product));
        $this->assertTrue($collection->products->contains($product));
    }

    public function test_a_product_can_have_several_authors_and_an_author_several_products(): void
    {
        $product = Product::factory()->create();
        $authors = Author::factory()->count(2)->create();

        $product->authors()->attach([
            $authors[0]->id => ['sort_order' => 0],
            $authors[1]->id => ['sort_order' => 1],
        ]);

        $this->assertCount(2, $product->refresh()->authors);
        $this->assertSame($authors[0]->name, $product->authors->first()->name);

        $other = Product::factory()->create();
        $other->authors()->attach($authors[0]);

        $this->assertCount(2, $authors[0]->refresh()->products);
    }

    public function test_the_taxonomy_relations_survive_the_deletion_of_a_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create();

        $category->delete();

        // El producto sigue en el catálogo, solo pierde la sección.
        $this->assertNull($product->refresh()->category_id);
    }

    public function test_categories_are_hierarchical(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->childOf($parent)->create();
        $grandchild = Category::factory()->childOf($child)->create();

        $this->assertTrue($child->parent->is($parent));
        $this->assertTrue($parent->children->contains($child));

        $this->assertEqualsCanonicalizing(
            [$parent->id, $child->id, $grandchild->id],
            $parent->descendantIds(),
        );
    }

    public function test_deleting_a_parent_promotes_its_children_instead_of_removing_them(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->childOf($parent)->create();

        $parent->delete();

        $this->assertNull($child->refresh()->parent_id);
    }

    public function test_images_belong_to_a_product_and_the_cover_is_the_first_one(): void
    {
        $product = Product::factory()->create();

        ProductImage::factory()->for($product)->create(['path' => '/b.jpg', 'sort_order' => 2]);
        ProductImage::factory()->for($product)->create(['path' => '/a.jpg', 'sort_order' => 1]);

        $this->assertCount(2, $product->images);
        $this->assertSame('/a.jpg', $product->cover->path);
    }

    public function test_deleting_a_product_removes_its_images_and_its_authorship(): void
    {
        $product = Product::factory()->create();
        $product->authors()->attach(Author::factory()->create());
        ProductImage::factory()->for($product)->create();

        $product->delete();

        $this->assertDatabaseCount('product_images', 0);
        $this->assertDatabaseCount('author_product', 0);
    }

    public function test_scopes_narrow_the_catalogue(): void
    {
        Product::factory()->count(3)->create();
        Product::factory()->inactive()->create();
        Product::factory()->featured()->create();
        Product::factory()->newRelease()->create();
        Product::factory()->outOfStock()->create();

        $this->assertSame(6, Product::query()->active()->count());
        $this->assertSame(1, Product::query()->featured()->count());
        $this->assertSame(1, Product::query()->newReleases()->count());
        $this->assertSame(6, Product::query()->inStock()->count());
    }

    public function test_new_releases_come_out_newest_first(): void
    {
        $old = Product::factory()->newRelease()->create(['published_at' => '2020-01-01']);
        $recent = Product::factory()->newRelease()->create(['published_at' => '2026-01-01']);

        $this->assertSame(
            [$recent->id, $old->id],
            Product::query()->newReleases()->pluck('id')->all(),
        );
    }

    public function test_active_categories_come_out_in_editorial_order(): void
    {
        Category::factory()->create(['name' => 'Segunda', 'sort_order' => 20]);
        Category::factory()->create(['name' => 'Primera', 'sort_order' => 10]);
        Category::factory()->inactive()->create(['sort_order' => 1]);

        $this->assertSame(
            ['Primera', 'Segunda'],
            Category::query()->active()->roots()->ordered()->pluck('name')->all(),
        );
    }

    public function test_the_price_is_stored_as_an_exact_decimal(): void
    {
        $product = Product::factory()->create(['price' => 12990]);

        // DECIMAL, no FLOAT: el valor vuelve exacto desde la base de datos.
        $this->assertSame('12990.00', $product->refresh()->price);

        // El frontend recibe un entero en la unidad menor de la moneda (CLP no
        // usa decimales, así que coincide con el peso).
        $this->assertSame(12990, $product->price_minor);
    }

    public function test_casts_return_the_expected_php_types(): void
    {
        $product = Product::factory()->create(['published_at' => '2026-03-14']);

        $this->assertIsBool($product->active);
        $this->assertIsBool($product->featured);
        $this->assertIsBool($product->new_release);
        $this->assertIsInt($product->stock);
        $this->assertSame('2026-03-14', $product->published_at->toDateString());
    }

    public function test_availability_follows_the_stock(): void
    {
        $threshold = (int) config('paulinas.catalog.low_stock_threshold');

        $this->assertSame('out_of_stock', Product::factory()->create(['stock' => 0])->availability);
        $this->assertSame('low_stock', Product::factory()->create(['stock' => $threshold])->availability);
        $this->assertSame('in_stock', Product::factory()->create(['stock' => $threshold + 1])->availability);
    }

    public function test_catalogue_models_are_addressed_by_slug(): void
    {
        $this->assertSame('slug', (new Product)->getRouteKeyName());
        $this->assertSame('slug', (new Category)->getRouteKeyName());
        $this->assertSame('slug', (new Author)->getRouteKeyName());
        $this->assertSame('slug', (new Publisher)->getRouteKeyName());
        $this->assertSame('slug', (new Collection)->getRouteKeyName());
    }
}
