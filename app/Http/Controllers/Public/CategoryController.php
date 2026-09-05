<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\ListsProducts;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    use ListsProducts;

    public function show(Request $request, Category $category): Response
    {
        abort_unless($category->active, 404);

        $category->load(['parent', 'children' => fn ($query) => $query->active()->ordered()]);

        $products = $this->paginateCards(
            Product::query()
                ->active()
                // La sección incluye lo que hay en sus subsecciones.
                ->whereIn('category_id', $category->descendantIds())
                ->orderByDesc('published_at')
                ->orderBy('title'),
            $request,
        );

        return Inertia::render('Catalog/Category', [
            'seo' => [
                'title' => $category->name,
                'description' => $category->description
                    ?? "Títulos de {$category->name} en Librerías Paulinas Chile.",
            ],
            'category' => [
                'name' => $category->name,
                'description' => $category->description,
                'parent' => $category->parent ? [
                    'name' => $category->parent->name,
                    'href' => route('categories.show', $category->parent, absolute: false),
                ] : null,
                'children' => $category->children->map(fn (Category $child) => [
                    'name' => $child->name,
                    'href' => route('categories.show', $child, absolute: false),
                ])->all(),
            ],
            'products' => $products,
        ]);
    }
}
