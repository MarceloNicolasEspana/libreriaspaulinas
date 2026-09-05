<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\ListsProducts;
use App\Models\Author;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuthorController extends Controller
{
    use ListsProducts;

    public function show(Request $request, Author $author): Response
    {
        $products = $this->paginateCards(
            Product::query()
                ->active()
                ->whereHas('authors', fn (Builder $query) => $query->whereKey($author->getKey()))
                ->orderByDesc('published_at')
                ->orderBy('title'),
            $request,
        );

        return Inertia::render('Catalog/Author', [
            'seo' => [
                'title' => $author->name,
                'description' => "Títulos de {$author->name} en Librerías Paulinas Chile.",
            ],
            'author' => [
                'name' => $author->name,
                'biography' => $author->biography,
                'image' => $author->image,
            ],
            'products' => $products,
        ]);
    }
}
