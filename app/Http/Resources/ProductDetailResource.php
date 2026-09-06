<?php

namespace App\Http\Resources;

use App\Models\Author;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ficha completa de un producto.
 *
 * @mixin Product
 */
class ProductDetailResource extends JsonResource
{
    /**
     * Relaciones necesarias para armar la ficha.
     *
     * @var array<int, string>
     */
    public const RELATIONS = ['authors', 'category.parent', 'publisher', 'collection', 'images'];

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'shortDescription' => $this->short_description,
            'description' => $this->description,
            'isbn' => $this->isbn,
            'price' => $this->price_minor,
            'pages' => $this->pages,
            'dimensions' => $this->dimensions,
            'availability' => $this->availability,
            'publishedAt' => $this->published_at?->toDateString(),
            'canAddToCart' => $this->stock > 0,
            'cartStoreHref' => route('cart.items.store', $this->resource, absolute: false),

            'authors' => $this->authors->map(fn (Author $author) => [
                'name' => $author->name,
                'href' => route('authors.show', $author, absolute: false),
            ])->all(),

            'category' => $this->category ? [
                'name' => $this->category->name,
                'href' => route('categories.show', $this->category, absolute: false),
                'parent' => $this->category->parent ? [
                    'name' => $this->category->parent->name,
                    'href' => route('categories.show', $this->category->parent, absolute: false),
                ] : null,
            ] : null,

            'publisher' => $this->publisher?->only('name', 'slug'),
            'collection' => $this->collection?->only('name', 'slug'),

            'images' => $this->images->map(fn (ProductImage $image) => [
                'path' => $image->url,
                'alt' => $image->alt ?? $this->title,
            ])->all(),
        ];
    }
}
