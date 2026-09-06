<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Post;
use App\Models\Product;
use App\Models\Publisher;
use App\Models\Resource;
use App\Models\ResourceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{string, class-string, string, bool}> */
    public static function sections(): array
    {
        return [
            'products' => ['productos', Product::class, 'title', true],
            'categories' => ['categorias', Category::class, 'name', true],
            'authors' => ['autores', Author::class, 'name', false],
            'publishers' => ['editoriales', Publisher::class, 'name', false],
            'collections' => ['colecciones', Collection::class, 'name', false],
            'branches' => ['librerias', Branch::class, 'name', true],
            'resources' => ['recursos', Resource::class, 'title', true],
            'resource categories' => ['categorias-recursos', ResourceCategory::class, 'name', false],
            'posts' => ['novedades', Post::class, 'title', true],
        ];
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    /** @return array<string, mixed> */
    private function payload(string $section): array
    {
        $base = ['name' => 'Nuevo registro', 'title' => 'Nuevo registro', 'slug' => '', 'active' => true, 'description' => 'Descripción de ejemplo', 'sort_order' => 0];

        return $base + match ($section) {
            'productos' => ['price' => '12990.50', 'stock' => 5, 'pages' => 120, 'author_ids' => [], 'featured' => false, 'new_release' => true],
            'librerias' => ['address' => 'Calle 123', 'commune' => 'Santiago', 'region' => 'Metropolitana', 'opening_hours' => [['days' => 'Lunes', 'periods' => ['09:00–18:00']]]],
            'recursos' => ['resource_category_id' => ResourceCategory::factory()->create()->id, 'published_at' => '2026-01-01 10:00:00'],
            'novedades' => ['excerpt' => 'Extracto editorial', 'content' => 'Contenido completo', 'published_at' => '2026-01-01 10:00:00'],
            default => [],
        };
    }

    #[DataProvider('sections')]
    public function test_admin_can_create_read_update_and_remove_records(string $section, string $model, string $title, bool $deactivate): void
    {
        $this->actingAs($this->admin());
        $data = $this->payload($section);
        $this->get('/admin/'.$section)->assertInertia(fn (Assert $page) => $page->component('Admin/Index'));
        $this->get('/admin/'.$section.'/crear')->assertInertia(fn (Assert $page) => $page->component('Admin/Edit')->where('recordId', null));
        $this->post('/admin/'.$section, $data)->assertSessionHasNoErrors()->assertRedirect();
        $record = $model::where('slug', 'nuevo-registro')->firstOrFail();
        $this->assertSame('Nuevo registro', $record->{$title});
        $this->get('/admin/'.$section.'/'.$record->id.'/editar')->assertInertia(fn (Assert $page) => $page->where('values.'.$title, 'Nuevo registro'));
        $this->put('/admin/'.$section.'/'.$record->id, [...$data, $title => 'Registro editado', 'slug' => 'slug-editable'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('Registro editado', $record->fresh()->{$title});
        $this->assertSame('slug-editable', $record->fresh()->slug);
        $this->delete('/admin/'.$section.'/'.$record->id, ['confirmed' => true])->assertRedirect('/admin/'.$section);
        if ($deactivate) {
            $this->assertFalse($record->fresh()->active);
            $this->assertModelExists($record);
            $this->put('/admin/'.$section.'/'.$record->id, [...$data, 'slug' => 'slug-editable'])->assertSessionHasNoErrors();
            $this->assertTrue($record->fresh()->active);
        } else {
            $this->assertModelMissing($record);
        }
    }

    #[DataProvider('sections')]
    public function test_every_crud_endpoint_requires_admin_permission(string $section, string $model, string $title, bool $deactivate): void
    {
        $user = User::factory()->create();
        $requests = [['get', '/admin/'.$section], ['get', '/admin/'.$section.'/crear'], ['get', '/admin/'.$section.'/1/editar'], ['post', '/admin/'.$section], ['put', '/admin/'.$section.'/1'], ['delete', '/admin/'.$section.'/1']];
        foreach ($requests as [$method, $url]) {
            $this->{$method}($url)->assertRedirect('/admin/login');
        }
        $this->actingAs($user);
        foreach ($requests as [$method, $url]) {
            $this->{$method}($url)->assertForbidden();
        }
        $this->assertDatabaseCount((new $model)->getTable(), 0);
    }

    #[DataProvider('sections')]
    public function test_invalid_input_and_duplicate_slugs_do_not_write_records(string $section, string $model, string $title, bool $deactivate): void
    {
        $this->actingAs($this->admin());
        $this->post('/admin/'.$section, [])->assertSessionHasErrors([$title => 'Este campo es obligatorio.']);
        $this->assertDatabaseCount((new $model)->getTable(), 0);
        $data = $this->payload($section);
        $this->post('/admin/'.$section, $data)->assertSessionHasNoErrors();
        $this->post('/admin/'.$section, $data)->assertSessionHasErrors(['slug' => 'Este valor ya está en uso.']);
        $this->assertSame(1, $model::where('slug', 'nuevo-registro')->count());
    }

    public function test_dashboard_counts_and_index_pagination(): void
    {
        $this->actingAs($this->admin());
        Product::factory()->count(21)->create();
        $this->get('/admin')->assertInertia(fn (Assert $page) => $page->component('Admin/Dashboard')->has('stats', 9)->where('stats.0.total', 21)->has('adminNavigation', 10));
        $this->get('/admin/productos')->assertInertia(fn (Assert $page) => $page->has('records.data', 20)->where('records.total', 21));
        $this->get('/admin/productos?page=2')->assertInertia(fn (Assert $page) => $page->has('records.data', 1));
        $this->get('/admin/usuarios')->assertNotFound();
        $this->get('/admin/productos/99999/editar')->assertNotFound();
    }

    public function test_product_saves_relationships_and_images_visible_in_public_catalogue(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());
        $author = Author::factory()->create();
        $category = Category::factory()->create();
        $publisher = Publisher::factory()->create();
        $collection = Collection::factory()->create();
        $data = [...$this->payload('productos'), 'author_ids' => [$author->id], 'category_id' => $category->id, 'publisher_id' => $publisher->id, 'collection_id' => $collection->id, 'images' => [UploadedFile::fake()->image('cover.jpg')]];
        $this->post('/admin/productos', $data)->assertSessionHasNoErrors();
        $product = Product::where('slug', 'nuevo-registro')->firstOrFail();
        $this->assertSame('12990.50', $product->price);
        $this->assertSame([$author->id], $product->authors()->pluck('authors.id')->all());
        $this->assertSame($category->id, $product->category_id);
        $this->assertSame($publisher->id, $product->publisher_id);
        $this->assertSame($collection->id, $product->collection_id);
        $image = $product->images()->firstOrFail();
        Storage::disk('public')->assertExists($image->path);
        $this->get('/libros/'.$product->slug)->assertInertia(fn (Assert $page) => $page->where('product.images.0.path', Storage::disk('public')->url($image->path)));
        unset($data['images']);
        $this->put('/admin/productos/'.$product->id, [...$data, 'slug' => 'nuevo-registro', 'author_ids' => [], 'remove_image_ids' => [$image->id]])->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($image->path);
        $this->assertSame(0, $product->authors()->count());
        $this->assertSame(0, $product->images()->count());
    }

    public function test_cannot_remove_another_products_image(): void
    {
        $this->actingAs($this->admin());
        $product = Product::factory()->create();
        $other = Product::factory()->hasImages(1)->create();
        $image = $other->images()->firstOrFail();
        $this->put('/admin/productos/'.$product->id, [...$this->payload('productos'), 'remove_image_ids' => [$image->id]])->assertSessionHasErrors('remove_image_ids.0');
        $this->assertModelExists($image);
    }

    public function test_resource_pdf_and_thumbnail_can_be_uploaded_replaced_and_removed(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $this->actingAs($this->admin());
        $data = $this->payload('recursos');
        $pdf = fn (): UploadedFile => UploadedFile::fake()->createWithContent('guide.pdf', file_get_contents(database_path('seeders/fixtures/catequesis.pdf')));
        $this->post('/admin/recursos', [...$data, 'file_upload' => $pdf(), 'image_upload' => UploadedFile::fake()->image('cover.png')])->assertSessionHasNoErrors();
        $resource = Resource::where('slug', 'nuevo-registro')->firstOrFail();
        $oldFile = $resource->file_path;
        $oldImage = $resource->thumbnail;
        Storage::disk('local')->assertExists($oldFile);
        Storage::disk('public')->assertExists($oldImage);
        $this->get('/recursos/nuevo-registro/descargar')->assertDownload(basename($oldFile));
        $this->put('/admin/recursos/'.$resource->id, [...$data, 'file_upload' => $pdf(), 'image_upload' => UploadedFile::fake()->image('new.jpg')])->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($oldFile);
        Storage::disk('public')->assertMissing($oldImage);
        $resource->refresh();
        $newFile = $resource->file_path;
        $this->put('/admin/recursos/'.$resource->id, [...$data, 'remove_file' => true, 'remove_image' => true])->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($newFile);
        $this->assertNull($resource->fresh()->file_path);
        $this->assertNull($resource->fresh()->thumbnail);
    }

    public function test_image_rejects_wrong_type_extension_size_and_null(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());
        $photo = UploadedFile::fake()->image('photo.jpg');
        $files = [
            UploadedFile::fake()->create('script.php', 1, 'text/plain'),
            new UploadedFile($photo->getPathname(), 'photo.php', null, null, true),
            UploadedFile::fake()->image('large.jpg')->size(5121),
            null,
        ];
        foreach ($files as $file) {
            $this->post('/admin/productos', [...$this->payload('productos'), 'images' => [$file]])->assertSessionHasErrors('images.0');
        }
        $this->assertDatabaseCount('products', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_pdf_rejects_non_pdf_and_oversized_files(): void
    {
        Storage::fake('local');
        $this->actingAs($this->admin());
        $data = $this->payload('recursos');
        $photo = UploadedFile::fake()->image('photo.jpg');
        foreach ([new UploadedFile($photo->getPathname(), 'fake.pdf', null, null, true), UploadedFile::fake()->create('large.pdf', 20481, 'application/pdf')] as $file) {
            $this->post('/admin/recursos', [...$data, 'file_upload' => $file])->assertSessionHasErrors('file_upload');
        }
        $this->assertDatabaseCount('resources', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_important_records_require_confirmation_and_keep_data_when_deactivated(): void
    {
        $this->actingAs($this->admin());
        $product = Product::factory()->create();
        $this->delete('/admin/productos/'.$product->id)->assertSessionHasErrors('confirmed');
        $this->assertTrue($product->fresh()->active);
        $this->delete('/admin/productos/'.$product->id, ['confirmed' => true])->assertSessionHas('success');
        $this->assertFalse($product->fresh()->active);
        $this->get('/libros/'.$product->slug)->assertNotFound();
    }

    public function test_related_taxonomies_cannot_be_deleted(): void
    {
        $this->actingAs($this->admin());
        $author = Author::factory()->create();
        $publisher = Publisher::factory()->create();
        $collection = Collection::factory()->create();
        Product::factory()->for($publisher)->for($collection)->hasAttached($author)->create();
        $category = ResourceCategory::factory()->create();
        Resource::factory()->for($category, 'category')->create();
        foreach (['autores' => $author, 'editoriales' => $publisher, 'colecciones' => $collection, 'categorias-recursos' => $category] as $section => $record) {
            $this->delete('/admin/'.$section.'/'.$record->id, ['confirmed' => true])->assertSessionHas('error');
            $this->assertModelExists($record);
        }
    }

    public function test_category_hierarchy_cannot_contain_cycles(): void
    {
        $this->actingAs($this->admin());
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $parent->id]);
        foreach ([$parent->id, $child->id] as $parentId) {
            $this->put('/admin/categorias/'.$parent->id, [...$this->payload('categorias'), 'parent_id' => $parentId])->assertSessionHasErrors('parent_id');
        }
        $this->assertNull($parent->fresh()->parent_id);
    }

    public function test_invalid_product_values_cannot_be_persisted(): void
    {
        $this->actingAs($this->admin());
        $this->post('/admin/productos', [...$this->payload('productos'), 'price' => -1, 'stock' => -1, 'pages' => 65536, 'category_id' => 9999, 'author_ids' => [9999], 'slug' => '../bad'])->assertSessionHasErrors(['price', 'stock', 'pages', 'category_id', 'author_ids.0', 'slug']);
        $this->assertDatabaseCount('products', 0);
    }

    public function test_server_ignores_unvalidated_storage_paths_and_ids(): void
    {
        $this->actingAs($this->admin());
        $this->post('/admin/novedades', [...$this->payload('novedades'), 'image' => 'malicious.php', 'id' => 99])->assertSessionHasNoErrors();
        $post = Post::where('slug', 'nuevo-registro')->firstOrFail();
        $this->assertNull($post->image);
        $this->assertNotSame(99, $post->id);
    }

    public function test_multipart_updates_accept_omitted_empty_arrays(): void
    {
        $this->actingAs($this->admin());
        $product = Product::factory()->hasAttached(Author::factory())->create();
        $data = $this->payload('productos');
        unset($data['author_ids']);

        $this->post('/admin/productos/'.$product->id, [...$data, '_method' => 'put'])
            ->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame(0, $product->authors()->count());
        $this->assertSame('Nuevo registro', $product->fresh()->title);
    }

    public function test_branch_can_save_without_opening_hours(): void
    {
        $this->actingAs($this->admin());
        $data = $this->payload('librerias');
        unset($data['opening_hours']);

        $this->post('/admin/librerias', $data)->assertSessionHasNoErrors();

        $this->assertSame([], Branch::where('slug', 'nuevo-registro')->firstOrFail()->opening_hours);
    }

    public function test_published_content_requires_a_date_and_drafts_do_not(): void
    {
        $this->actingAs($this->admin());
        foreach (['recursos' => Resource::class, 'novedades' => Post::class] as $section => $model) {
            $data = [...$this->payload($section), 'published_at' => null];
            $this->post('/admin/'.$section, $data)->assertSessionHasErrors('published_at');
            $this->assertSame(0, $model::count());
            $this->post('/admin/'.$section, [...$data, 'active' => false])->assertSessionHasNoErrors();
            $this->assertFalse($model::firstOrFail()->active);
        }
    }
}
