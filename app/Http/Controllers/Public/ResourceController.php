<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\EducationalResource;
use App\Models\Resource;
use App\Models\ResourceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResourceController extends Controller
{
    public function index(Request $request): Response
    {
        $categorySlug = $request->string('categoria')->toString();
        $resources = Resource::query()->published()->with('category')
            ->when($categorySlug !== '', fn ($query) => $query->whereHas('category', fn ($categories) => $categories->where('slug', $categorySlug)))
            ->latest('published_at')->orderByDesc('id')->paginate(12)->withQueryString()
            ->through(fn (Resource $resource) => (new EducationalResource($resource))->resolve($request));

        return Inertia::render('Resources/Index', [
            'resources' => $resources,
            'selectedCategory' => $categorySlug,
            'indexHref' => route('resources.index', absolute: false),
            'categories' => ResourceCategory::orderBy('name')->get()->map(fn (ResourceCategory $category) => [
                'name' => $category->name, 'slug' => $category->slug,
                'href' => route('resources.index', ['categoria' => $category->slug], absolute: false),
            ]),
        ]);
    }

    public function show(Request $request, Resource $resource): Response
    {
        abort_unless(Resource::query()->published()->whereKey($resource->id)->exists(), 404);
        $resource->load('category');

        return Inertia::render('Resources/Show', [
            'resource' => (new EducationalResource($resource))->resolve($request),
            'indexHref' => route('resources.index', absolute: false),
        ]);
    }

    public function download(Resource $resource): StreamedResponse
    {
        abort_unless(Resource::query()->published()->whereKey($resource->id)->exists(), 404);
        abort_unless($resource->hasDownload(), 404);

        return Storage::disk('local')->download($resource->file_path, basename($resource->file_path), ['X-Content-Type-Options' => 'nosniff']);
    }
}
