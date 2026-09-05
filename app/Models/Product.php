<?php

namespace App\Models;

use App\Support\Money;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // "decimal:2" mantiene el precio como cadena exacta y evita que PHP
            // lo convierta a float por el camino.
            'price' => 'decimal:2',
            'pages' => 'integer',
            'stock' => 'integer',
            'active' => 'boolean',
            'featured' => 'boolean',
            'new_release' => 'boolean',
            'published_at' => 'date',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class)
            ->withPivot('sort_order')
            ->orderBy('author_product.sort_order');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Portada: la primera imagen del orden establecido.
     */
    public function cover(): HasOne
    {
        return $this->hasOne(ProductImage::class)->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }

    public function scopeFeatured(Builder $query): void
    {
        $query->where('featured', true);
    }

    /**
     * Novedades, de la edición más reciente a la más antigua.
     */
    public function scopeNewReleases(Builder $query): void
    {
        $query->where('new_release', true)->orderByDesc('published_at');
    }

    public function scopeInStock(Builder $query): void
    {
        $query->where('stock', '>', 0);
    }

    /**
     * Precio en la unidad menor de la moneda, que es lo que consume el
     * frontend (ver App\Support\Money y el componente ProductCard).
     */
    protected function priceMinor(): Attribute
    {
        return Attribute::get(fn (): int => Money::toMinor($this->price));
    }

    /**
     * Estado que muestra la tarjeta: disponible, últimas unidades o bajo pedido.
     */
    protected function availability(): Attribute
    {
        return Attribute::get(function (): string {
            if ($this->stock <= 0) {
                return 'out_of_stock';
            }

            return $this->stock <= config('paulinas.catalog.low_stock_threshold')
                ? 'low_stock'
                : 'in_stock';
        });
    }
}
