<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeleteRecordRequest;
use App\Http\Requests\Admin\SaveRecordRequest;
use App\Models\Product;
use App\Support\Admin\Catalog;
use App\Support\Admin\SaveRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class RecordController extends Controller
{
    public function index(string $section): Response
    {
        $definition = Catalog::definition($section);
        $columns = ['id', $definition['title'], 'slug', 'updated_at'];
        if ($definition['deactivate']) {
            $columns[] = 'active';
        }

        return Inertia::render('Admin/Index', [
            'section' => $section, 'heading' => $definition['label'], 'deactivate' => $definition['deactivate'],
            'createHref' => route('admin.records.create', $section, absolute: false),
            'records' => Catalog::model($section)->newQuery()->select($columns)->orderByDesc('id')->paginate(20)->through(fn (Model $record): array => [
                'id' => $record->id, 'title' => $record->getAttribute($definition['title']), 'slug' => $record->slug,
                'active' => $definition['deactivate'] ? (bool) $record->active : null,
                'editHref' => route('admin.records.edit', [$section, $record->id], absolute: false),
                'deleteHref' => route('admin.records.destroy', [$section, $record->id], absolute: false),
            ]),
        ]);
    }

    public function create(string $section): Response
    {
        return $this->form($section, Catalog::model($section));
    }

    public function edit(string $section, int $record): Response
    {
        return $this->form($section, Catalog::model($section)->newQuery()->findOrFail($record));
    }

    public function store(SaveRecordRequest $request, string $section, SaveRecord $save): RedirectResponse
    {
        $record = $save->handle($section, Catalog::model($section), $request->validated());

        return to_route('admin.records.edit', [$section, $record->id])->with('success', 'Registro creado correctamente.');
    }

    public function update(SaveRecordRequest $request, string $section, int $record, SaveRecord $save): RedirectResponse
    {
        $save->handle($section, Catalog::model($section)->newQuery()->findOrFail($record), $request->validated());

        return to_route('admin.records.edit', [$section, $record])->with('success', 'Cambios guardados.');
    }

    public function destroy(DeleteRecordRequest $request, string $section, int $record): RedirectResponse
    {
        $definition = Catalog::definition($section);
        $message = DB::transaction(function () use ($section, $record, $definition): string {
            $model = Catalog::model($section)->newQuery()->lockForUpdate()->findOrFail($record);
            if ($definition['deactivate']) {
                $model->update(['active' => false]);

                return 'Registro desactivado. Puedes volver a activarlo desde Editar.';
            }
            $relation = $section === 'categorias-recursos' ? 'resources' : 'products';
            if ($model->{$relation}()->exists()) {
                return 'No se puede eliminar: este registro está en uso. Modifica primero sus asociaciones.';
            }
            $model->delete();

            return 'Registro eliminado.';
        });
        Cache::forget('public-sitemap');

        return to_route('admin.records.index', $section)->with(str_starts_with($message, 'No se') ? 'error' : 'success', $message);
    }

    private function form(string $section, Model $record): Response
    {
        $definition = Catalog::definition($section);
        $fields = Catalog::fields($section);
        $values = [];
        foreach ($fields as $field) {
            $name = $field['name'];
            $default = match ($field['type']) {
                'checkbox' => false, 'hours', 'multiselect', 'images' => [], 'number' => in_array($name, ['price', 'stock', 'sort_order'], true) ? 0 : '', default => ''
            };
            $value = $record->getAttributes()[$name] ?? $default;
            if ($field['type'] === 'checkbox') {
                $value = (bool) $value;
            }
            if ($record->exists && $name === 'opening_hours') {
                $value = $record->opening_hours;
            }
            if ($value && $field['type'] === 'datetime-local') {
                $value = str_replace(' ', 'T', substr((string) $value, 0, 16));
            }
            if ($value && $field['type'] === 'date') {
                $value = substr((string) $value, 0, 10);
            }
            $values[$name] = $value ?? '';
        }
        $gallery = [];
        if ($record instanceof Product && $record->exists) {
            $values['author_ids'] = $record->authors()->pluck('authors.id')->all();
            $gallery = $record->images()->get()->map(fn ($image): array => ['id' => $image->id, 'url' => $image->url, 'alt' => $image->alt])->all();
        }
        $imageColumn = $section === 'recursos' ? 'thumbnail' : 'image';
        $image = $record->getAttributes()[$imageColumn] ?? null;

        return Inertia::render('Admin/Edit', [
            'heading' => $definition['label'], 'section' => $section, 'recordId' => $record->id,
            'fields' => $fields, 'values' => $values, 'gallery' => $gallery,
            'imageUrl' => $image ? (str_starts_with($image, 'admin/') ? Storage::disk('public')->url($image) : $image) : null,
            'fileName' => isset($record->getAttributes()['file_path']) ? basename($record->file_path) : null,
            'indexHref' => route('admin.records.index', $section, absolute: false),
            'submitHref' => $record->exists ? route('admin.records.update', [$section, $record->id], absolute: false) : route('admin.records.store', $section, absolute: false),
        ]);
    }
}
