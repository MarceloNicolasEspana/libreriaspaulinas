<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\ListsProducts;
use App\Models\Author;
use App\Models\Product;
use App\Support\Seo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AuthorController extends Controller
{
    use ListsProducts;

    public function show(Request $request, Author $author): Response
    {
        $image = $author->image && str_starts_with($author->image, 'admin/')
            ? Storage::disk('public')->url($author->image) : $author->image;

        $products = $this->paginateCards(
            Product::query()
                ->active()
                ->whereHas('authors', fn (Builder $query) => $query->whereKey($author->getKey()))
                ->orderByDesc('published_at')
                ->orderBy('title'),
            $request,
        );

        return Inertia::render('Catalog/Author', [
            'seo' => Seo::page(
                $request,
                $author->name,
                "Títulos de {$author->name} en Librerías Paulinas Chile.",
                [
                    ['name' => 'Inicio', 'href' => '/'],
                    ['name' => 'Libros', 'href' => '/libros'],
                    ['name' => $author->name, 'href' => route('authors.show', $author, absolute: false)],
                ],
                image: $image,
            ),
            'author' => [
                'name' => $author->name,
                'biography' => $author->biography,
                'image' => $image,
            ],
            'products' => $products,
        ]);
    }
}
