<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Producto tal como lo consume el componente ProductCard.
 *
 * Es el contrato entre el catálogo y la interfaz: la portada, el listado de
 * /libros, la sección y la página de autor entregan todas esta misma forma.
 *
 * @mixin Product
 */
class ProductCardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->authors->pluck('name')->join(', ') ?: null,
            'category' => $this->category?->name,
            'categoryHref' => $this->category
                ? route('categories.show', $this->category, absolute: false)
                : null,

            // Monto entero en la unidad menor de la moneda (ver App\Support\Money).
            'price' => $this->price_minor,

            'availability' => $this->availability,
            'cover' => $this->cover?->path,
            'href' => route('books.show', $this->resource, absolute: false),
        ];
    }

    /**
     * Columnas de "products" que la tarjeta usa.
     *
     * Un listado no necesita la descripción larga ni las dimensiones, y traerlas
     * multiplica el peso de cada página por nada. Los criterios de orden
     * (published_at, featured, new_release) no se seleccionan: ORDER BY puede
     * usar columnas que no están en el SELECT.
     *
     * @var array<int, string>
     */
    public const COLUMNS = [
        'products.id',
        'products.title',
        'products.slug',
        'products.price',
        'products.stock',
        'products.category_id',
    ];

    /**
     * Relaciones que la tarjeta necesita, con las columnas de cada una.
     *
     * Se declaran aquí para que ningún listado las olvide y termine haciendo
     * N+1. Los nombres van calificados porque estas consultas conviven con
     * JOIN sobre la tabla intermedia de autores.
     *
     * @var array<int, string>
     */
    public const RELATIONS = [
        'authors:authors.id,authors.name',
        'category:id,name,slug',
        // product_id y sort_order no se muestran, pero sin ellos la relación no
        // sabe a qué producto pertenece la imagen ni cuál es la primera.
        'cover:id,product_id,path,sort_order',
    ];
}
