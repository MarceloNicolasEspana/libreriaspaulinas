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
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * Estado del catálogo leído desde la query string.
 *
 * Todo el estado del listado vive en la URL, no en el componente: así una
 * búsqueda se puede compartir, el botón "atrás" del navegador funciona y cada
 * combinación de filtros es una dirección indexable.
 *
 *     /libros?categoria=biblias&autor=ana-maria-herrera&orden=precio-asc&page=2
 *
 * El objeto es inmutable. "except()" devuelve una copia sin ciertos filtros,
 * que es lo que necesita CatalogOptions para contar cada faceta.
 */
final class CatalogFilters
{
    public const SEARCH = 'q';

    public const SORT = 'orden';

    public const PRICE_MIN = 'precio_min';

    public const PRICE_MAX = 'precio_max';

    public const AVAILABILITY = 'disponibilidad';

    /**
     * Filtros que resuelven un slug contra una tabla: clave en la URL => modelo.
     *
     * @var array<string, class-string<Model>>
     */
    public const TAXONOMIES = [
        'categoria' => Category::class,
        'autor' => Author::class,
        'editorial' => Publisher::class,
        'coleccion' => BookCollection::class,
    ];

    /**
     * Estados de disponibilidad por los que se puede filtrar.
     *
     * Son dos y no tres a propósito. La tarjeta distingue además "últimas
     * unidades", pero eso es un aviso, no una intención de compra: quien filtra
     * quiere saber qué puede llevarse hoy y qué hay que encargar.
     *
     * @var array<string, string>
     */
    public const AVAILABILITY_OPTIONS = [
        'disponible' => 'Disponible',
        'bajo-pedido' => 'Bajo pedido',
    ];

    /**
     * Criterios de orden ofrecidos, en el orden en que se muestran.
     *
     * @var array<string, string>
     */
    public const SORT_OPTIONS = [
        'relevancia' => 'Relevancia',
        'recientes' => 'Más recientes',
        'precio-asc' => 'Precio: menor a mayor',
        'precio-desc' => 'Precio: mayor a menor',
        'az' => 'Título: A-Z',
    ];

    public const DEFAULT_SORT = 'relevancia';

    /**
     * Largo máximo aceptado en los campos de texto de la URL.
     */
    private const MAX_LENGTH = 120;

    /**
     * @param  array<string, mixed>  $values  Valores tal como viajan en la URL.
     * @param  array<string, Model>  $records  Modelos ya resueltos de las taxonomías.
     */
    private function __construct(
        private readonly array $values,
        private readonly array $records,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $values = [];
        $records = [];

        $search = self::text($request->input(self::SEARCH));

        if ($search !== null) {
            $values[self::SEARCH] = $search;
        }

        foreach (self::TAXONOMIES as $key => $model) {
            $slug = self::text($request->input($key));
            $record = $slug === null ? null : self::resolve($model, $slug);

            /*
             * Un slug que no corresponde a nada se descarta en silencio en vez
             * de responder 404 o de forzar un listado vacío: una dirección
             * compartida con una errata, o con una sección ya retirada, debe
             * seguir mostrando catálogo.
             */
            if ($record === null) {
                continue;
            }

            $values[$key] = $slug;
            $records[$key] = $record;
        }

        $availability = self::text($request->input(self::AVAILABILITY));

        if ($availability !== null && isset(self::AVAILABILITY_OPTIONS[$availability])) {
            $values[self::AVAILABILITY] = $availability;
        }

        [$min, $max] = self::priceRange($request);

        if ($min !== null) {
            $values[self::PRICE_MIN] = $min;
        }

        if ($max !== null) {
            $values[self::PRICE_MAX] = $max;
        }

        $sort = self::text($request->input(self::SORT));
        $values[self::SORT] = isset(self::SORT_OPTIONS[$sort]) ? $sort : self::DEFAULT_SORT;

        return new self($values, $records);
    }

    /**
     * Copia sin los filtros indicados.
     */
    public function except(string ...$keys): self
    {
        return new self(
            Arr::except($this->values, $keys),
            Arr::except($this->records, $keys),
        );
    }

    public function search(): ?string
    {
        return $this->values[self::SEARCH] ?? null;
    }

    public function sort(): string
    {
        return $this->values[self::SORT] ?? self::DEFAULT_SORT;
    }

    public function value(string $key): mixed
    {
        return $this->values[$key] ?? null;
    }

    public function record(string $key): ?Model
    {
        return $this->records[$key] ?? null;
    }

    /**
     * Hay algo que limpiar. El orden no cuenta: cambiarlo no esconde títulos.
     */
    public function isFiltered(): bool
    {
        return $this->except(self::SORT)->values !== [];
    }

    /**
     * Filtros y orden. Es lo que consume el listado de /libros.
     *
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function applyTo(Builder $query): Builder
    {
        $this->applyFilters($query);
        $this->applySort($query);

        return $query;
    }

    /**
     * Solo las restricciones, sin orden. Es lo que necesita CatalogOptions para
     * contar cada faceta.
     *
     * @param  Builder<Product>  $query
     */
    public function applyFilters(Builder $query): void
    {
        if (($search = $this->search()) !== null) {
            ProductSearch::apply($query, $search);
        }

        foreach (array_keys(self::TAXONOMIES) as $key) {
            if (($record = $this->record($key)) !== null) {
                $this->applyTaxonomy($query, $key, $record);
            }
        }

        match ($this->value(self::AVAILABILITY)) {
            'disponible' => $query->where('products.stock', '>', 0),
            'bajo-pedido' => $query->where('products.stock', '<=', 0),
            default => null,
        };

        /*
         * En la URL el precio viaja en la unidad menor de la moneda, la misma
         * en la que el frontend recibe y muestra los montos. La columna guarda
         * la unidad mayor, así que la comparación se hace convertida.
         */
        if (($min = $this->value(self::PRICE_MIN)) !== null) {
            $query->where('products.price', '>=', Money::toMajor($min));
        }

        if (($max = $this->value(self::PRICE_MAX)) !== null) {
            $query->where('products.price', '<=', Money::toMajor($max));
        }
    }

    /**
     * Estado para el frontend, con las mismas claves que la URL.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $keys = [
            self::SEARCH,
            ...array_keys(self::TAXONOMIES),
            self::AVAILABILITY,
            self::PRICE_MIN,
            self::PRICE_MAX,
        ];

        return collect($keys)
            ->mapWithKeys(fn (string $key) => [$key => $this->value($key)])
            ->put(self::SORT, $this->sort())
            ->all();
    }

    /**
     * Filtros activos con etiqueta legible, para las marcas que se muestran
     * sobre la cuadrícula y se pueden quitar de a una.
     *
     * @return array<int, array{key: string, label: string, value: string}>
     */
    public function chips(): array
    {
        $labels = [
            'categoria' => 'Sección',
            'autor' => 'Autor',
            'editorial' => 'Editorial',
            'coleccion' => 'Colección',
        ];

        $chips = [];

        if (($search = $this->search()) !== null) {
            $chips[] = ['key' => self::SEARCH, 'label' => 'Búsqueda', 'value' => $search];
        }

        foreach ($labels as $key => $label) {
            if (($record = $this->record($key)) !== null) {
                $chips[] = ['key' => $key, 'label' => $label, 'value' => $record->name];
            }
        }

        if (($availability = $this->value(self::AVAILABILITY)) !== null) {
            $chips[] = [
                'key' => self::AVAILABILITY,
                'label' => 'Disponibilidad',
                'value' => self::AVAILABILITY_OPTIONS[$availability],
            ];
        }

        $min = $this->value(self::PRICE_MIN);
        $max = $this->value(self::PRICE_MAX);

        if ($min !== null || $max !== null) {
            $chips[] = [
                // Los dos extremos se quitan juntos: son un solo rango.
                'key' => self::PRICE_MIN.','.self::PRICE_MAX,
                'label' => 'Precio',
                'value' => match (true) {
                    $min !== null && $max !== null => Money::format($min).' a '.Money::format($max),
                    $min !== null => 'Desde '.Money::format($min),
                    default => 'Hasta '.Money::format($max),
                },
            ];
        }

        return $chips;
    }

    /**
     * @param  Builder<Product>  $query
     */
    private function applyTaxonomy(Builder $query, string $key, Model $record): void
    {
        match ($key) {
            // La sección incluye lo que cuelga de ella, igual que en /categorias.
            'categoria' => $query->whereIn('products.category_id', $record->descendantIds()),
            'autor' => $query->whereHas('authors', fn (Builder $authors) => $authors->whereKey($record->getKey())),
            'editorial' => $query->where('products.publisher_id', $record->getKey()),
            'coleccion' => $query->where('products.collection_id', $record->getKey()),
            default => null,
        };
    }

    /**
     * @param  Builder<Product>  $query
     */
    private function applySort(Builder $query): void
    {
        match ($this->sort()) {
            'recientes' => $query->orderByDesc('products.published_at'),
            'precio-asc' => $query->orderBy('products.price')->orderBy('products.title'),
            'precio-desc' => $query->orderByDesc('products.price')->orderBy('products.title'),
            'az' => $query->orderBy('products.title'),
            default => $this->applyRelevance($query),
        };

        // Desempate estable: sin él, dos títulos del mismo precio pueden cambiar
        // de página entre una carga y la siguiente.
        $query->orderBy('products.id');
    }

    /**
     * @param  Builder<Product>  $query
     */
    private function applyRelevance(Builder $query): void
    {
        if (($search = $this->search()) !== null) {
            ProductSearch::applyRelevance($query, $search);
        } else {
            // Sin búsqueda, "relevancia" es el criterio editorial de la casa:
            // primero lo destacado, después la novedad, después lo más reciente.
            $query
                ->orderByDesc('products.featured')
                ->orderByDesc('products.new_release');
        }

        $query->orderByDesc('products.published_at')->orderBy('products.title');
    }

    /**
     * @param  class-string<Model>  $model
     */
    private static function resolve(string $model, string $slug): ?Model
    {
        $query = $model::query()->where('slug', $slug);

        // Solo las categorías se dan de baja; el resto de las taxonomías no
        // tiene estado de publicación.
        if ($model === Category::class) {
            $query->where('active', true);
        }

        return $query->first();
    }

    /**
     * Rango de precio en la unidad menor de la moneda, con los extremos
     * ordenados: un mínimo mayor que el máximo es un dedazo, no una petición de
     * cero resultados.
     *
     * @return array{0: ?int, 1: ?int}
     */
    private static function priceRange(Request $request): array
    {
        $min = self::amount($request->input(self::PRICE_MIN));
        $max = self::amount($request->input(self::PRICE_MAX));

        if ($min !== null && $max !== null && $min > $max) {
            return [$max, $min];
        }

        return [$min, $max];
    }

    private static function amount(mixed $value): ?int
    {
        if (! is_scalar($value) || ! is_numeric($value)) {
            return null;
        }

        $amount = (int) $value;

        return $amount >= 0 ? $amount : null;
    }

    private static function text(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $text = trim(mb_substr($value, 0, self::MAX_LENGTH));

        return $text === '' ? null : $text;
    }
}
