<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Models\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = Cache::remember('public-sitemap', now()->addHour(), function (): array {
            $static = [
                ['loc' => route('home'), 'lastmod' => null],
                ['loc' => route('books.index'), 'lastmod' => null],
                ['loc' => route('branches.index'), 'lastmod' => null],
                ['loc' => route('resources.index'), 'lastmod' => null],
                ['loc' => route('posts.index'), 'lastmod' => null],
                ['loc' => route('about'), 'lastmod' => null],
                ['loc' => route('contact.index'), 'lastmod' => null],
            ];

            return collect($static)
                ->concat($this->modelUrls(Category::query()->active(), 'categories.show'))
                ->concat($this->modelUrls(
                    Author::query()->whereHas('products', fn (Builder $query) => $query->active()),
                    'authors.show',
                ))
                ->concat($this->modelUrls(Product::query()->active(), 'books.show'))
                ->concat($this->modelUrls(Branch::query()->active(), 'branches.show'))
                ->concat($this->modelUrls(Resource::query()->published(), 'resources.show'))
                ->concat($this->modelUrls(Post::query()->published(), 'posts.show'))
                ->all();
        });

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * @param  Builder<Model>  $query
     * @return array<int, array{loc: string, lastmod: ?string}>
     */
    private function modelUrls(Builder $query, string $route): array
    {
        return $query->orderBy('id')->get(['id', 'slug', 'updated_at'])
            ->map(fn ($model): array => [
                'loc' => route($route, $model),
                'lastmod' => $model->updated_at?->toAtomString(),
            ])
            ->all();
    }
}
