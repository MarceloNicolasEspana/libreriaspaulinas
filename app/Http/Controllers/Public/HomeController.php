<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\BranchResource;
use App\Http\Resources\ProductCardResource;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Support\DemoContent;
use App\Support\Seo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Cuántas piezas muestra cada franja de la portada.
     */
    private const NEW_RELEASES = 8;

    private const FEATURED = 4;

    /**
     * Muestra la portada del sitio público.
     *
     * El catálogo llega de la base de datos. Las campañas del hero, los accesos
     * a recursos y las librerías siguen en App\Support\DemoContent porque
     * todavía no tienen modelo.
     */
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Home', [
            'seo' => Seo::page(
                $request,
                'Inicio',
                'Librerías Paulinas Chile: libros, material pastoral y recursos para la evangelización y la educación religiosa.',
                schemas: [Seo::organization()],
            ),

            'heroBanners' => DemoContent::heroBanners(),
            'newReleases' => $this->cards($request, fn (Builder $query) => $query->newReleases(), self::NEW_RELEASES),
            'categories' => $this->categories(),
            'featured' => $this->cards($request, fn (Builder $query) => $query->featured(), self::FEATURED),
            'resources' => DemoContent::teachingResources(),
            'branches' => BranchResource::collection(Branch::query()->active()->ordered()->limit(3)->get())->resolve($request),
        ]);
    }

    /**
     * @param  callable(Builder<Product>): mixed  $filter
     * @return array<int, array<string, mixed>>
     */
    private function cards(Request $request, callable $filter, int $limit): array
    {
        $query = Product::query()->active()->with(ProductCardResource::RELATIONS);

        $filter($query);

        return $query->limit($limit)->get()
            ->map(fn (Product $product) => (new ProductCardResource($product))->toArray($request))
            ->all();
    }

    /**
     * Secciones de primer nivel con su número de títulos.
     *
     * El recuento incluye las subsecciones, igual que la página de sección: se
     * resuelve con una sola consulta agregada en lugar de una por tarjeta.
     *
     * @return array<int, array<string, mixed>>
     */
    private function categories(): array
    {
        $totals = Product::query()
            ->active()
            ->whereNotNull('category_id')
            ->toBase()
            ->selectRaw('category_id, count(*) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $roots = Category::query()
            ->active()
            ->roots()
            ->ordered()
            // Dos niveles: descendantIds() recorre el árbol y sin esto haría
            // una consulta por subsección.
            ->with([
                'children' => fn (Relation $query) => $query->active(),
                'children.children' => fn (Relation $query) => $query->active(),
            ])
            ->get();

        return $roots->map(fn (Category $root) => [
            'name' => $root->name,
            'href' => route('categories.show', $root, absolute: false),
            'count' => $this->totalFor($root, $totals),
        ])->all();
    }

    /**
     * @param  Collection<int, int>  $totals
     */
    private function totalFor(Category $category, Collection $totals): int
    {
        return collect($category->descendantIds())
            ->sum(fn (int $id) => (int) $totals->get($id, 0));
    }
}
