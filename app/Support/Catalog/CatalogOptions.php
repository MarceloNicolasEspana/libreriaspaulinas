<?php

namespace App\Support\Catalog;

use App\Models\Author;
use App\Models\Category;
use App\Models\Collection as BookCollection;
use App\Models\Product;
use App\Models\Publisher;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Opciones que ofrece la barra de filtros del catálogo.
 *
 * Cada faceta se cuenta contra el resto de los filtros activos, pero no contra
 * el propio: estando en "Biblias", el listado de secciones sigue mostrando
 * cuántos títulos hay en "Catequesis". Si se contara también el filtro propio,
 * todas las demás secciones marcarían cero y la barra dejaría de servir para
 * navegar.
 *
 * Se descartan las opciones sin resultados, salvo la que está seleccionada:
 * ofrecer un filtro que lleva a una página vacía es una promesa incumplida,
 * pero hacer desaparecer el filtro que el usuario acaba de poner es peor.
 *
 * Son unas pocas consultas de agregación por carga, todas sobre columnas
 * indexadas. Se prefieren a contar en PHP porque contar en PHP obligaría a
 * traer el catálogo entero.
 */
final class CatalogOptions
{
    /**
     * Facetas anunciadas en el diseño que todavía no tienen dónde apoyarse en
     * la base de datos.
     *
     * Viajan al frontend como "pendientes" para que la barra las muestre
     * deshabilitadas en vez de omitirlas. No se inventan columnas ni valores
     * para completarlas: cuando exista la taxonomía real, cada una pasa a
     * CatalogFilters::TAXONOMIES y sale de esta lista.
     *
     * @var array<string, string>
     */
    public const PENDING = [
        'edad' => 'Edad recomendada',
        'sacramento' => 'Sacramento',
        'tematica' => 'Temática',
    ];

    private function __construct(private readonly CatalogFilters $filters) {}

    /**
     * @return array<string, mixed>
     */
    public static function for(CatalogFilters $filters): array
    {
        $options = new self($filters);

        return [
            'categorias' => $options->categories(),
            'autores' => $options->taxonomy(Author::class, 'autor'),
            'editoriales' => $options->taxonomy(Publisher::class, 'editorial'),
            'colecciones' => $options->taxonomy(BookCollection::class, 'coleccion'),
            'disponibilidad' => $options->availability(),
            'precio' => $options->priceBounds(),
            'orden' => self::labelled(CatalogFilters::SORT_OPTIONS),
            'pendientes' => self::labelled(self::PENDING),
        ];
    }

    /**
     * Árbol de secciones con el total de cada rama.
     *
     * El total de una sección incluye el de sus subsecciones, porque filtrar
     * por ella también las incluye.
     *
     * @return array<int, array<string, mixed>>
     */
    private function categories(): array
    {
        $counts = $this->counts('categoria', 'products.category_id');
        $selected = $this->filters->value('categoria');

        $categories = Category::query()
            ->active()
            ->ordered()
            ->get(['id', 'name', 'slug', 'parent_id']);

        $byParent = $categories->groupBy('parent_id');

        /**
         * Arma una rama y poda las hijas vacías. Devuelve null si la rama
         * entera está vacía y no es la seleccionada.
         *
         * @return array<string, mixed>|null
         */
        $branch = function (Category $category) use (&$branch, $byParent, $counts, $selected): ?array {
            $children = collect($byParent->get($category->id, []))
                ->map(fn (Category $child) => $branch($child))
                ->filter()
                ->values()
                ->all();

            $total = (int) ($counts[$category->id] ?? 0) + array_sum(array_column($children, 'count'));

            if ($total === 0 && $category->slug !== $selected) {
                return null;
            }

            return [
                'value' => $category->slug,
                'label' => $category->name,
                'count' => $total,
                'children' => $children,
            ];
        };

        return $categories
            ->whereNull('parent_id')
            ->map(fn (Category $root) => $branch($root))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Facetas de un solo nivel: autor, editorial y colección.
     *
     * @param  class-string<Model>  $model
     * @return array<int, array<string, mixed>>
     */
    private function taxonomy(string $model, string $key): array
    {
        $counts = match ($key) {
            // La relación con el autor es N:M: se cuenta sobre la intermedia.
            'autor' => $this->countsOver('autor', 'author_product', 'author_id'),
            'editorial' => $this->counts('editorial', 'products.publisher_id'),
            default => $this->counts('coleccion', 'products.collection_id'),
        };

        $selected = $this->filters->value($key);

        $records = $model::query()
            ->whereKey($counts->keys()->all())
            ->when($selected !== null, fn ($query) => $query->orWhere('slug', $selected))
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return $records
            ->map(fn (Model $record) => [
                'value' => $record->slug,
                'label' => $record->name,
                'count' => (int) ($counts[$record->getKey()] ?? 0),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function availability(): array
    {
        $row = $this->query(CatalogFilters::AVAILABILITY)
            ->selectRaw('sum(case when products.stock > 0 then 1 else 0 end) as available')
            ->selectRaw('sum(case when products.stock <= 0 then 1 else 0 end) as backorder')
            ->first();

        $counts = [
            'disponible' => (int) ($row?->available ?? 0),
            'bajo-pedido' => (int) ($row?->backorder ?? 0),
        ];

        $selected = $this->filters->value(CatalogFilters::AVAILABILITY);

        return collect(CatalogFilters::AVAILABILITY_OPTIONS)
            ->map(fn (string $label, string $value) => [
                'value' => $value,
                'label' => $label,
                'count' => $counts[$value],
            ])
            ->filter(fn (array $option) => $option['count'] > 0 || $option['value'] === $selected)
            ->values()
            ->all();
    }

    /**
     * Extremos del precio, en la unidad menor de la moneda, para acotar los dos
     * campos del formulario. Se calculan con el resto de los filtros puestos:
     * el rango que ofrece la barra es el rango que de verdad existe.
     *
     * @return array{min: int, max: int}
     */
    private function priceBounds(): array
    {
        $row = $this->query(CatalogFilters::PRICE_MIN, CatalogFilters::PRICE_MAX)
            ->selectRaw('min(products.price) as low, max(products.price) as high')
            ->first();

        return [
            'min' => Money::toMinor($row?->low ?? 0),
            'max' => Money::toMinor($row?->high ?? 0),
        ];
    }

    /**
     * Totales por valor de una columna de "products".
     *
     * @return Collection<int, int>
     */
    private function counts(string $key, string $column): Collection
    {
        return $this->query($key)
            ->select($column)
            ->selectRaw('count(*) as aggregate')
            ->groupBy($column)
            ->pluck('aggregate', $column)
            ->map(fn ($total) => (int) $total);
    }

    /**
     * Totales por valor de una columna de una tabla intermedia.
     *
     * @return Collection<int, int>
     */
    private function countsOver(string $key, string $table, string $column): Collection
    {
        return $this->query($key)
            ->join($table, "{$table}.product_id", '=', 'products.id')
            ->select("{$table}.{$column}")
            ->selectRaw('count(*) as aggregate')
            ->groupBy("{$table}.{$column}")
            ->pluck('aggregate', $column)
            ->map(fn ($total) => (int) $total);
    }

    /**
     * Consulta base para contar una faceta: catálogo activo con todos los
     * filtros vigentes salvo los que se indiquen.
     *
     * @return Builder<Product>
     */
    private function query(string ...$except): Builder
    {
        $query = Product::query()->active();

        $this->filters->except(...$except)->applyFilters($query);

        return $query;
    }

    /**
     * @param  array<string, string>  $labels
     * @return array<int, array{value: string, label: string}>
     */
    private static function labelled(array $labels): array
    {
        return collect($labels)
            ->map(fn (string $label, string $value) => ['value' => $value, 'label' => $label])
            ->values()
            ->all();
    }
}
