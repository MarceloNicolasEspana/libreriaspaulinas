<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Resource;
use App\Models\ResourceCategory;
use Database\Seeders\PostSeeder;
use Database\Seeders\ResourceCategorySeeder;
use Database\Seeders\ResourceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EducationalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_resources_filter_by_category_and_paginate_in_publication_order(): void
    {
        $this->freezeTime();
        $category = ResourceCategory::factory()->create(['slug' => 'biblia', 'name' => 'Biblia']);
        Resource::factory()->count(13)->for($category, 'category')->create();
        $newest = Resource::factory()->for($category, 'category')->create(['published_at' => now()]);
        Resource::factory()->create();
        $this->get('/recursos?categoria=biblia')->assertInertia(fn (Assert $page) => $page
            ->component('Resources/Index')->where('resources.total', 14)->has('resources.data', 12)
            ->where('resources.data.0.title', $newest->title)->where('resources.data.0.category', 'Biblia')
            ->where('resources.data.0.href', '/recursos/'.$newest->slug)
            ->where('resources.next_page_url', fn ($url) => str_contains($url, 'categoria=biblia') && str_contains($url, 'page=2')));
        $this->get('/recursos?categoria=biblia&page=2')->assertInertia(fn (Assert $page) => $page->has('resources.data', 2));
    }

    public function test_posts_paginate_newest_first_without_sending_full_content(): void
    {
        $this->freezeTime();
        Post::factory()->count(9)->create();
        $newest = Post::factory()->create(['published_at' => now()]);
        $this->get('/novedades')->assertInertia(fn (Assert $page) => $page
            ->component('Posts/Index')->has('posts.data', 9)->where('posts.total', 10)
            ->where('posts.data.0.title', $newest->title)->missing('posts.data.0.content'));
        $this->get('/novedades?page=2')->assertInertia(fn (Assert $page) => $page->has('posts.data', 1));
    }

    /** @return array<string, array{class-string, string, string, array<string, mixed>}> */
    public static function hiddenContent(): array
    {
        $cases = [];
        foreach ([Resource::class => ['/recursos', 'resources'], Post::class => ['/novedades', 'posts']] as $model => [$path, $prop]) {
            foreach (['inactive' => ['active' => false], 'draft' => ['published_at' => null], 'scheduled' => ['published_at' => '2099-01-01']] as $state => $attributes) {
                $cases[$prop.' '.$state] = [$model, $path, $prop, $attributes];
            }
        }

        return $cases;
    }

    #[DataProvider('hiddenContent')]
    public function test_unpublished_content_is_hidden(string $model, string $path, string $prop, array $attributes): void
    {
        $this->freezeTime();
        $record = $model::factory()->create($attributes);
        $this->get($path)->assertInertia(fn (Assert $page) => $page->where($prop.'.total', 0));
        $this->get($path.'/'.$record->slug)->assertNotFound();
    }

    public function test_resource_has_an_html_detail_with_a_working_pdf_download(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('resources/encuentro.pdf', '%PDF-1.4 sample');
        $resource = Resource::factory()->create(['file_path' => 'resources/encuentro.pdf', 'thumbnail' => 'resources/cover.jpg']);
        $this->get('/recursos/'.$resource->slug)->assertInertia(fn (Assert $page) => $page
            ->component('Resources/Show')->where('resource.title', $resource->title)
            ->where('resource.description', $resource->description)->where('resource.audience', $resource->audience)
            ->where('resource.file', 'encuentro.pdf')->where('resource.downloadHref', '/recursos/'.$resource->slug.'/descargar')
            ->where('resource.thumbnail', fn ($url) => str_ends_with($url, '/storage/resources/cover.jpg'))
            ->missing('resource.file_path'));
        $this->get('/recursos/'.$resource->slug.'/descargar')->assertDownload('encuentro.pdf')->assertStreamedContent('%PDF-1.4 sample');
    }

    /** @return array<string, array{?string}> */
    public static function unavailableFiles(): array
    {
        return ['none' => [null], 'missing' => ['resources/missing.pdf'], 'outside folder' => ['secrets.pdf'], 'traversal' => ['resources/../secrets.pdf'], 'windows traversal' => ['resources/..\\secrets.pdf']];
    }

    #[DataProvider('unavailableFiles')]
    public function test_unavailable_or_unsafe_files_have_no_download(?string $path): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('secrets.pdf', 'private');
        $resource = Resource::factory()->create(['file_path' => $path]);
        $this->get('/recursos/'.$resource->slug)->assertInertia(fn (Assert $page) => $page->where('resource.downloadHref', null)->where('resource.file', null));
        $this->get('/recursos/'.$resource->slug.'/descargar')->assertNotFound();
    }

    public function test_unpublished_resources_cannot_be_downloaded(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('resources/private.pdf', 'private');
        foreach ([['active' => false], ['published_at' => null], ['published_at' => now()->addDay()]] as $attributes) {
            $resource = Resource::factory()->create([...$attributes, 'file_path' => 'resources/private.pdf']);
            $this->get('/recursos/'.$resource->slug.'/descargar')->assertNotFound();
        }
    }

    public function test_post_detail_contains_the_full_article_and_image(): void
    {
        $post = Post::factory()->create(['content' => "Primer párrafo.\n\nSegundo párrafo.", 'image' => 'posts/cover.jpg']);
        $this->get('/novedades/'.$post->slug)->assertInertia(fn (Assert $page) => $page
            ->component('Posts/Show')->where('post.title', $post->title)->where('post.excerpt', $post->excerpt)
            ->where('post.content', "Primer párrafo.\n\nSegundo párrafo.")
            ->where('post.image', fn ($url) => str_ends_with($url, '/storage/posts/cover.jpg'))
            ->where('indexHref', '/novedades'));
    }

    public function test_empty_lists_and_unknown_slugs(): void
    {
        $this->get('/recursos')->assertInertia(fn (Assert $page) => $page->component('Resources/Index')->has('resources.data', 0));
        $this->get('/recursos?categoria=inexistente')->assertInertia(fn (Assert $page) => $page->has('resources.data', 0));
        $this->get('/novedades')->assertInertia(fn (Assert $page) => $page->component('Posts/Index')->has('posts.data', 0));
        $this->get('/recursos/inexistente')->assertNotFound();
        $this->get('/novedades/inexistente')->assertNotFound();
    }

    public function test_seed_content_is_repeatable_and_navigable(): void
    {
        Storage::fake('local');
        $this->seed([ResourceCategorySeeder::class, ResourceSeeder::class, PostSeeder::class]);
        $this->seed([ResourceCategorySeeder::class, ResourceSeeder::class, PostSeeder::class]);
        $this->assertDatabaseCount('resource_categories', 7);
        $this->assertDatabaseCount('resources', 1);
        $this->assertDatabaseCount('posts', 1);
        $this->get('/recursos/preparar-un-encuentro-de-catequesis')->assertOk();
        $this->get('/recursos/preparar-un-encuentro-de-catequesis/descargar')->assertDownload('preparar-un-encuentro-de-catequesis.pdf');
        $this->get('/novedades/leer-y-compartir-en-comunidad')->assertOk();
    }
}
