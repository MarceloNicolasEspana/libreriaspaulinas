<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Posts/Index', [
            'seo' => Seo::page(
                $request,
                'Novedades',
                'Lecturas, encuentros y vida de comunidad. Descubre las novedades de Paulinas.',
                [['name' => 'Inicio', 'href' => '/'], ['name' => 'Novedades', 'href' => '/novedades']],
            ),
            'posts' => Post::query()->published()->select(['id', 'title', 'slug', 'excerpt', 'image', 'published_at'])
                ->latest('published_at')->orderByDesc('id')->paginate(9)
                ->through(fn (Post $post) => (new PostResource($post))->resolve($request)),
        ]);
    }

    public function show(Request $request, Post $post): Response
    {
        abort_unless(Post::query()->published()->whereKey($post->id)->exists(), 404);

        return Inertia::render('Posts/Show', [
            'seo' => Seo::page(
                $request,
                $post->title,
                $post->excerpt,
                [
                    ['name' => 'Inicio', 'href' => '/'],
                    ['name' => 'Novedades', 'href' => '/novedades'],
                    ['name' => $post->title, 'href' => route('posts.show', $post, absolute: false)],
                ],
                [Seo::article($post)],
                $post->image ? Storage::disk('public')->url($post->image) : null,
                'article',
            ),
            'post' => (new PostResource($post))->resolve($request),
            'indexHref' => route('posts.index', absolute: false),
        ]);
    }
}
