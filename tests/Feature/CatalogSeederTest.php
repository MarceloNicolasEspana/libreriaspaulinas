<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\Publisher;
use Database\Factories\ProductFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El catálogo de demostración tiene que dejar el sitio en un estado navegable:
 * secciones con títulos, novedades, destacados y los tres estados de stock.
 */
class CatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Se siembra en cada caso y no con $seed: con SQLite en memoria, ese
     * atributo solo tiene efecto en la primera clase de prueba del proceso.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_it_seeds_between_thirty_and_fifty_products(): void
    {
        $this->assertGreaterThanOrEqual(30, Product::query()->count());
        $this->assertLessThanOrEqual(50, Product::query()->count());
    }

    public function test_it_seeds_the_whole_taxonomy(): void
    {
        $this->assertSame(12, Category::query()->roots()->count());
        $this->assertGreaterThan(12, Category::query()->count(), 'Faltan subsecciones.');
        $this->assertGreaterThanOrEqual(6, Publisher::query()->count());
        $this->assertGreaterThanOrEqual(8, Collection::query()->count());
        $this->assertGreaterThanOrEqual(15, Author::query()->count());
    }

    public function test_every_section_has_titles(): void
    {
        foreach (Category::query()->roots()->get() as $section) {
            $this->assertGreaterThan(
                0,
                Product::query()->whereIn('category_id', $section->descendantIds())->count(),
                "La sección \"{$section->name}\" quedó vacía.",
            );
        }
    }

    public function test_it_seeds_the_states_the_home_page_needs(): void
    {
        $this->assertSame(8, Product::query()->active()->newReleases()->count());
        $this->assertGreaterThanOrEqual(4, Product::query()->active()->featured()->count());
        $this->assertGreaterThan(0, Product::query()->where('active', false)->count());
    }

    public function test_every_availability_state_is_represented(): void
    {
        $states = Product::query()->get()->map->availability->unique()->values()->all();

        $this->assertEqualsCanonicalizing(['in_stock', 'low_stock', 'out_of_stock'], $states);
    }

    public function test_every_product_has_an_author_and_a_publisher(): void
    {
        $this->assertSame(0, Product::query()->doesntHave('authors')->count());
        $this->assertSame(0, Product::query()->whereNull('publisher_id')->count());
    }

    public function test_isbns_are_unique_and_well_formed(): void
    {
        $isbns = Product::query()->whereNotNull('isbn')->pluck('isbn');

        $this->assertGreaterThan(0, $isbns->count());
        $this->assertCount($isbns->count(), $isbns->unique());

        foreach ($isbns as $isbn) {
            $this->assertMatchesRegularExpression('/^978-956-\d{2}-\d{4}-\d$/', $isbn);
            $this->assertTrue($this->isbnCheckDigitIsValid($isbn), "ISBN inválido: {$isbn}");
        }
    }

    /**
     * Todo lo sembrado se declara como demostración: nada debe pasar por
     * catálogo oficial.
     */
    public function test_seeded_products_declare_themselves_as_demo_content(): void
    {
        $products = Product::query()->whereNotNull('description')->get();

        $this->assertGreaterThan(0, $products->count());

        foreach ($products as $product) {
            $this->assertStringContainsString(ProductFactory::DEMO_NOTICE, $product->description);
        }
    }

    /**
     * Ninguna ficha apunta a una imagen que no existe: sin fotografías, la
     * portada la dibuja el cliente.
     */
    public function test_it_does_not_seed_images_that_are_not_on_disk(): void
    {
        $this->assertDatabaseCount('product_images', 0);
    }

    public function test_running_the_seeder_twice_does_not_duplicate_the_catalogue(): void
    {
        $products = Product::query()->count();
        $categories = Category::query()->count();

        $this->seed();

        $this->assertSame($products, Product::query()->count());
        $this->assertSame($categories, Category::query()->count());
    }

    private function isbnCheckDigitIsValid(string $isbn): bool
    {
        $digits = preg_replace('/\D/', '', $isbn);
        $sum = 0;

        foreach (str_split($digits) as $position => $digit) {
            $sum += ((int) $digit) * ($position % 2 === 0 ? 1 : 3);
        }

        return $sum % 10 === 0;
    }
}
