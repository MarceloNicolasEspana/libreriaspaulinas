<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Posts/Index', [
            'posts' => Post::query()->published()->select(['id', 'title', 'slug', 'excerpt', 'image', 'published_at'])
                ->latest('published_at')->orderByDesc('id')->paginate(9)
                ->through(fn (Post $post) => (new PostResource($post))->resolve($request)),
        ]);
    }

    public function show(Request $request, Post $post): Response
    {
        abort_unless(Post::query()->published()->whereKey($post->id)->exists(), 404);

        return Inertia::render('Posts/Show', [
            'post' => (new PostResource($post))->resolve($request),
            'indexHref' => route('posts.index', absolute: false),
        ]);
    }
}
