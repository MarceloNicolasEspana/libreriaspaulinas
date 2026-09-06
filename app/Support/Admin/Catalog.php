<?php

namespace App\Support\Admin;

use App\Models\Author;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Post;
use App\Models\Product;
use App\Models\Publisher;
use App\Models\Resource;
use App\Models\ResourceCategory;
use Illuminate\Database\Eloquent\Model;

class Catalog
{
    /** @var array<string, array{model: class-string<Model>, label: string, title: string, deactivate: bool}> */
    public const SECTIONS = [
        'productos' => ['model' => Product::class, 'label' => 'Productos', 'title' => 'title', 'deactivate' => true],
        'categorias' => ['model' => Category::class, 'label' => 'Categorías', 'title' => 'name', 'deactivate' => true],
        'autores' => ['model' => Author::class, 'label' => 'Autores', 'title' => 'name', 'deactivate' => false],
        'editoriales' => ['model' => Publisher::class, 'label' => 'Editoriales', 'title' => 'name', 'deactivate' => false],
        'colecciones' => ['model' => Collection::class, 'label' => 'Colecciones', 'title' => 'name', 'deactivate' => false],
        'librerias' => ['model' => Branch::class, 'label' => 'Librerías', 'title' => 'name', 'deactivate' => true],
        'recursos' => ['model' => Resource::class, 'label' => 'Recursos', 'title' => 'title', 'deactivate' => true],
        'categorias-recursos' => ['model' => ResourceCategory::class, 'label' => 'Categorías de recursos', 'title' => 'name', 'deactivate' => false],
        'novedades' => ['model' => Post::class, 'label' => 'Novedades', 'title' => 'title', 'deactivate' => true],
    ];

    /** @return array{model: class-string<Model>, label: string, title: string, deactivate: bool} */
    public static function definition(string $section): array
    {
        abort_unless(isset(self::SECTIONS[$section]), 404);

        return self::SECTIONS[$section];
    }

    public static function model(string $section): Model
    {
        $class = self::definition($section)['model'];

        return new $class;
    }

    /** @return array<int, array{label: string, href: string}> */
    public static function navigation(): array
    {
        $items = [['label' => 'Resumen', 'href' => route('admin.dashboard', absolute: false)]];
        foreach (self::SECTIONS as $section => $definition) {
            $items[] = ['label' => $definition['label'], 'href' => route('admin.records.index', $section, absolute: false)];
        }

        return $items;
    }

    /** @return array<int, array<string, mixed>> */
    public static function fields(string $section): array
    {
        $title = self::definition($section)['title'];
        $fields = [[$title, $title === 'title' ? 'Título' : 'Nombre', 'text'], ['slug', 'Slug', 'text']];
        $extra = match ($section) {
            'productos' => [
                ['author_ids', 'Autores', 'multiselect', Author::class],
                ['short_description', 'Descripción breve', 'textarea'], ['description', 'Descripción', 'textarea'],
                ['isbn', 'ISBN', 'text'], ['price', 'Precio (CLP)', 'number'], ['pages', 'Páginas', 'number'],
                ['dimensions', 'Dimensiones', 'text'], ['stock', 'Stock', 'number'],
                ['category_id', 'Categoría', 'select', Category::class], ['publisher_id', 'Editorial', 'select', Publisher::class],
                ['collection_id', 'Colección', 'select', Collection::class], ['published_at', 'Fecha de edición', 'date'],
                ['active', 'Activo', 'checkbox'], ['featured', 'Destacado', 'checkbox'], ['new_release', 'Novedad', 'checkbox'],
                ['images', 'Añadir imágenes', 'images'],
            ],
            'categorias' => [['description', 'Descripción', 'textarea'], ['parent_id', 'Categoría superior', 'select', Category::class], ['sort_order', 'Orden', 'number'], ['active', 'Activa', 'checkbox'], ['image_upload', 'Imagen', 'image']],
            'autores' => [['biography', 'Biografía', 'textarea'], ['image_upload', 'Imagen', 'image']],
            'editoriales', 'colecciones' => [['description', 'Descripción', 'textarea']],
            'librerias' => [
                ['address', 'Dirección', 'text'], ['commune', 'Comuna', 'text'], ['region', 'Región', 'text'],
                ['phone', 'Teléfono', 'tel'], ['whatsapp', 'WhatsApp', 'tel'], ['email', 'Correo', 'email'],
                ['opening_hours', 'Horarios de atención', 'hours'], ['latitude', 'Latitud', 'number'], ['longitude', 'Longitud', 'number'],
                ['sort_order', 'Orden', 'number'], ['active', 'Activa', 'checkbox'],
            ],
            'recursos' => [
                ['description', 'Descripción', 'textarea'], ['resource_category_id', 'Categoría', 'select', ResourceCategory::class],
                ['audience', 'Público destinatario', 'text'], ['published_at', 'Fecha de publicación', 'datetime-local'],
                ['active', 'Activo', 'checkbox'], ['image_upload', 'Imagen', 'image'], ['file_upload', 'Archivo PDF', 'file'],
            ],
            'novedades' => [['excerpt', 'Extracto', 'textarea'], ['content', 'Contenido', 'textarea'], ['published_at', 'Fecha de publicación', 'datetime-local'], ['active', 'Activa', 'checkbox'], ['image_upload', 'Imagen', 'image']],
            default => [],
        };

        return array_map(function (array $field): array {
            [$name, $label, $type] = $field;
            $result = ['name' => $name, 'label' => $label, 'type' => $type];
            if (isset($field[3])) {
                $result['options'] = $field[3]::orderBy('name')->get(['id', 'name'])->toArray();
            }

            return $result;
        }, [...$fields, ...$extra]);
    }
}
