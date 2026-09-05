<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\ListsProducts;
use App\Http\Resources\ProductCardResource;
use App\Http\Resources\ProductDetailResource;
use App\Models\Category;
use App\Models\Product;
use App\Support\Catalog\CatalogFilters;
use App\Support\Catalog\CatalogOptions;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookController extends Controller
{
    use ListsProducts;

    /**
     * Cuántas secciones se proponen cuando una búsqueda no devuelve nada.
     */
    private const SUGGESTIONS = 6;

    /**
     * Catálogo completo, con búsqueda, filtros y orden.
     *
     * Todo se resuelve en la base de datos y todo queda escrito en la URL. El
     * navegador no recibe el catálogo para filtrarlo por su cuenta: recibe la
     * página que pidió.
     */
    public function index(Request $request): Response
    {
        $filters = CatalogFilters::fromRequest($request);

        $products = $this->paginateCards(
            $filters->applyTo(Product::query()->active()),
            $request,
        );

        return Inertia::render('Catalog/Index', [
            'seo' => $this->seo($filters),
            'heading' => $filters->search() !== null ? 'Resultados de búsqueda' : 'Libros',
            'subtitle' => $filters->search() !== null
                ? "Títulos que coinciden con «{$filters->search()}»."
                : 'Todo el fondo editorial disponible en nuestras librerías.',
            'products' => $products,
            'filters' => $filters->toArray(),
            'options' => CatalogOptions::for($filters),
            'chips' => $filters->chips(),
            // Cuando no hay resultados, la página ofrece por dónde seguir en vez
            // de dejar al visitante en un callejón sin salida.
            'suggestions' => $products->total() === 0 ? $this->suggestions() : [],
        ]);
    }

    /**
     * Ficha de un producto.
     *
     * Las fichas inactivas no son visibles: devuelven 404 igual que un slug
     * inexistente, para no revelar material todavía en preparación.
     */
    public function show(Request $request, Product $product): Response
    {
        abort_unless($product->active, 404);

        $product->load(ProductDetailResource::RELATIONS);

        return Inertia::render('Catalog/Show', [
            'seo' => [
                'title' => $product->title,
                'description' => $product->short_description
                    ?? "{$product->title}, disponible en Librerías Paulinas Chile.",
            ],
            // resolve(): Inertia envolvería el recurso en "data" si se le
            // entregara sin resolver, y la página espera el objeto plano.
            'product' => (new ProductDetailResource($product))->resolve(),
            'related' => $this->related($request, $product),
        ]);
    }

    /**
     * Título y descripción de la página, que cambian con la búsqueda para que
     * cada consulta compartida se presente por lo que es.
     *
     * @return array<string, string>
     */
    private function seo(CatalogFilters $filters): array
    {
        if (($search = $filters->search()) !== null) {
            return [
                'title' => "Búsqueda: {$search}",
                'description' => "Resultados para «{$search}» en el catálogo de Librerías Paulinas Chile.",
            ];
        }

        if (($category = $filters->record('categoria')) !== null) {
            return [
                'title' => "Libros de {$category->name}",
                'description' => "Títulos de {$category->name} en el catálogo de Librerías Paulinas Chile.",
            ];
        }

        return [
            'title' => 'Libros',
            'description' => 'Catálogo de libros de Librerías Paulinas Chile: Biblias, '
                .'catequesis, espiritualidad, liturgia y formación.',
        ];
    }

    /**
     * Secciones propuestas para el estado vacío: las que más títulos publicados
     * tienen, de cualquier nivel.
     *
     * No se limita a las secciones raíz porque nada obliga a que un producto
     * cuelgue de una: si el catálogo real clasifica solo en subsecciones, las
     * raíces quedarían vacías y la propuesta también.
     *
     * @return array<int, array<string, string>>
     */
    private function suggestions(): array
    {
        $active = fn ($query) => $query->where('active', true);

        return Category::query()
            ->active()
            // whereHas y no having: SQLite exige GROUP BY antes de HAVING.
            ->whereHas('products', $active)
            ->withCount(['products' => $active])
            ->orderByDesc('products_count')
            ->orderBy('name')
            ->limit(self::SUGGESTIONS)
            ->get(['id', 'name', 'slug'])
            ->map(fn (Category $category) => [
                'name' => $category->name,
                // Apunta al catálogo filtrado y no a /categorias/{slug}: desde
                // ahí el visitante puede seguir ajustando la búsqueda.
                'href' => route('books.index', ['categoria' => $category->slug], absolute: false),
            ])
            ->all();
    }

    /**
     * Otros títulos de la misma sección.
     *
     * @return array<int, array<string, mixed>>
     */
    private function related(Request $request, Product $product): array
    {
        if ($product->category_id === null) {
            return [];
        }

        return Product::query()
            ->active()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->getKey())
            ->select(ProductCardResource::COLUMNS)
            ->with(ProductCardResource::RELATIONS)
            ->inRandomOrder()
            ->limit(4)
            ->get()
            ->map(fn (Product $related) => (new ProductCardResource($related))->toArray($request))
            ->all();
    }
}
