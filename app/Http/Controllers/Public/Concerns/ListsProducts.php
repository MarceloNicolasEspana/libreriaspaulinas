<?php

namespace App\Http\Controllers\Public\Concerns;

use App\Http\Resources\ProductCardResource;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Listado paginado de productos en la forma que consume ProductCard.
 *
 * Lo comparten /libros, la página de sección y la de autor: los tres muestran
 * la misma cuadrícula y deben paginar igual.
 */
trait ListsProducts
{
    /**
     * @param  Builder<Product>  $query
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    protected function paginateCards(Builder $query, Request $request): LengthAwarePaginator
    {
        return $query
            ->select(ProductCardResource::COLUMNS)
            ->with(ProductCardResource::RELATIONS)
            ->paginate(config('paulinas.catalog.per_page'))
            ->withQueryString()
            ->through(fn (Product $product) => (new ProductCardResource($product))->toArray($request));
    }
}
