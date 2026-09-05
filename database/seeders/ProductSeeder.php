<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Category;
use App\Models\Collection as BookCollection;
use App\Models\Product;
use App\Models\Publisher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * Catálogo de demostración.
 *
 * TODOS los productos que crea este seeder son ficticios: los títulos se
 * generan por combinación, los ISBN llevan un registrante que no existe y los
 * precios son referenciales. Cada ficha lo declara en su descripción
 * (ProductFactory::DEMO_NOTICE). Nada de esto proviene del catálogo oficial y
 * todo se reemplaza cuando llegue la carga real.
 *
 * No se siembran imágenes: el catálogo todavía no tiene fotografías y el
 * componente BookCover dibuja la portada cuando no hay archivo. Sembrar rutas
 * a imágenes inexistentes solo produciría portadas rotas.
 */
class ProductSeeder extends Seeder
{
    /**
     * Productos por cada sección de primer nivel.
     */
    private const PER_SECTION = 3;

    public function run(): void
    {
        // El seeder está pensado para una base recién migrada; si ya hay
        // catálogo no se duplica.
        if (Product::query()->exists()) {
            return;
        }

        $publishers = Publisher::all();
        $collections = BookCollection::all();
        $authors = Author::all();

        $roots = Category::query()->roots()->ordered()->get();
        $children = Category::query()->whereNotNull('parent_id')->with('parent')->get();

        /*
         * Tres títulos por sección de primer nivel, más uno en algunas
         * subsecciones: así ninguna página de sección queda vacía y la
         * jerarquía se ve funcionando.
         */
        $targets = $roots->flatMap(fn (Category $root) => array_fill(0, self::PER_SECTION, $root))
            ->concat($children->random(min(6, $children->count())));

        $products = $targets->map(fn (Category $category) => $this->product($category, $publishers, $collections));

        $this->flag($products);
        $this->attachAuthors($products, $authors);
    }

    /**
     * @param  Collection<int, Publisher>  $publishers
     * @param  Collection<int, BookCollection>  $collections
     */
    private function product(Category $category, Collection $publishers, Collection $collections): Product
    {
        $factory = Product::factory()
            ->for($category)
            ->for($publishers->random())
            ->for($collections->random());

        // Los artículos de regalo no son libros: ni ISBN ni paginación.
        if ($this->sectionOf($category) === 'regalos') {
            $factory = $factory->withoutIsbn();
        }

        return $factory->create();
    }

    /**
     * Reparte los estados que la portada necesita mostrar: novedades,
     * destacados, quiebres de stock y fichas todavía sin publicar.
     *
     * @param  Collection<int, Product>  $products
     */
    private function flag(Collection $products): void
    {
        $pool = $products->shuffle();

        $pool->take(8)->each(fn (Product $product) => $product->update([
            'new_release' => true,
            'published_at' => now()->subMonths(random_int(0, 7))->toDateString(),
        ]));

        $pool->slice(8, 6)->each(fn (Product $product) => $product->update(['featured' => true]));

        $pool->slice(14, 3)->each(fn (Product $product) => $product->update(['stock' => 0]));

        $pool->slice(17, 4)->each(fn (Product $product) => $product->update([
            'stock' => random_int(1, (int) config('paulinas.catalog.low_stock_threshold')),
        ]));

        // Dos fichas en preparación, para comprobar que los listados públicos
        // filtran por "active".
        $pool->slice(21, 2)->each(fn (Product $product) => $product->update(['active' => false]));
    }

    /**
     * @param  Collection<int, Product>  $products
     * @param  Collection<int, Author>  $authors
     */
    private function attachAuthors(Collection $products, Collection $authors): void
    {
        foreach ($products as $product) {
            $selected = $authors->random(random_int(1, 2))->values();

            $product->authors()->attach(
                $selected->mapWithKeys(fn (Author $author, int $index) => [
                    $author->id => ['sort_order' => $index],
                ])->all()
            );
        }
    }

    /**
     * Slug de la sección de primer nivel a la que pertenece la categoría.
     */
    private function sectionOf(Category $category): string
    {
        return $category->parent_id === null
            ? $category->slug
            : (string) $category->parent?->slug;
    }
}
