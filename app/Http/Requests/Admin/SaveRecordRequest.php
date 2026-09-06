<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use App\Support\Admin\Catalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-admin') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $arrayField = match ($this->route('section')) {
            'productos' => 'author_ids',
            'librerias' => 'opening_hours',
            default => null,
        };
        if ($arrayField && ! $this->has($arrayField)) {
            $this->merge([$arrayField => []]);
        }
        $title = Catalog::definition($this->route('section'))['title'];
        if (! $this->filled('slug') && is_string($this->input($title))) {
            $this->merge(['slug' => Str::slug($this->input($title))]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $section = $this->route('section');
        $model = Catalog::model($section);
        $record = $this->route('record') ? $model->newQuery()->findOrFail($this->route('record')) : null;
        $rules = [
            Catalog::definition($section)['title'] => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique($model->getTable(), 'slug')->ignore($record)],
        ];
        $text = ['nullable', 'string', 'max:50000'];
        $boolean = ['required', 'boolean'];
        $image = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'];
        $order = ['required', 'integer', 'between:0,65535'];
        $rules += match ($section) {
            'productos' => [
                'short_description' => ['nullable', 'string', 'max:300'], 'description' => $text,
                'isbn' => ['nullable', 'string', 'max:20', 'regex:/^[0-9Xx -]+$/', Rule::unique('products', 'isbn')->ignore($record)],
                'price' => ['required', 'numeric', 'decimal:0,2', 'between:0,99999999.99'],
                'pages' => ['nullable', 'integer', 'between:1,65535'],
                'dimensions' => ['nullable', 'string', 'max:60'],
                'stock' => ['required', 'integer', 'between:0,4294967295'],
                'category_id' => ['nullable', 'integer', 'exists:categories,id'],
                'publisher_id' => ['nullable', 'integer', 'exists:publishers,id'],
                'collection_id' => ['nullable', 'integer', 'exists:collections,id'],
                'author_ids' => ['present', 'array', 'max:50'], 'author_ids.*' => ['integer', 'distinct', 'exists:authors,id'],
                'active' => $boolean, 'featured' => $boolean, 'new_release' => $boolean,
                'published_at' => ['nullable', 'date'],
                'images' => ['sometimes', 'array', 'max:10'], 'images.*' => ['required', ...array_slice($image, 1)],
                'remove_image_ids' => ['sometimes', 'array', 'max:100'],
                'remove_image_ids.*' => ['integer', 'distinct', Rule::exists('product_images', 'id')->where('product_id', $record?->id ?? 0)],
            ],
            'categorias' => [
                'description' => $text, 'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
                'sort_order' => $order, 'active' => $boolean, 'image_upload' => $image, 'remove_image' => ['sometimes', 'boolean'],
            ],
            'autores' => ['biography' => $text, 'image_upload' => $image, 'remove_image' => ['sometimes', 'boolean']],
            'editoriales', 'colecciones' => ['description' => $text],
            'librerias' => [
                'address' => ['required', 'string', 'max:255'], 'commune' => ['required', 'string', 'max:255'], 'region' => ['required', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:30', 'regex:/^[+0-9() -]+$/'], 'whatsapp' => ['nullable', 'string', 'max:30', 'regex:/^[+0-9() -]+$/'],
                'email' => ['nullable', 'email', 'max:255'],
                'opening_hours' => ['present', 'array', 'max:14'],
                'opening_hours.*' => ['array:days,periods'],
                'opening_hours.*.days' => ['required', 'string', 'max:100'],
                'opening_hours.*.periods' => ['required', 'array', 'min:1', 'max:4'],
                'opening_hours.*.periods.*' => ['required', 'string', 'max:80'],
                'latitude' => ['nullable', 'numeric', 'between:-90,90'], 'longitude' => ['nullable', 'numeric', 'between:-180,180'],
                'sort_order' => $order, 'active' => $boolean,
            ],
            'recursos' => [
                'description' => ['required', 'string', 'max:50000'],
                'resource_category_id' => ['required', 'integer', 'exists:resource_categories,id'],
                'audience' => ['nullable', 'string', 'max:255'],
                'published_at' => ['nullable', Rule::requiredIf(fn (): bool => $this->boolean('active')), 'date'], 'active' => $boolean,
                'image_upload' => $image, 'remove_image' => ['sometimes', 'boolean'],
                'file_upload' => ['nullable', 'file', 'mimes:pdf', 'extensions:pdf', 'max:20480'],
                'remove_file' => ['sometimes', 'boolean'],
            ],
            'novedades' => [
                'excerpt' => ['required', 'string', 'max:2000'], 'content' => ['required', 'string', 'max:200000'],
                'published_at' => ['nullable', Rule::requiredIf(fn (): bool => $this->boolean('active')), 'date'], 'active' => $boolean,
                'image_upload' => $image, 'remove_image' => ['sometimes', 'boolean'],
            ],
            default => [],
        };

        return $rules;
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->route('section') !== 'categorias' || $validator->errors()->has('parent_id')) {
                return;
            }
            $parent = $this->input('parent_id');
            $visited = [(int) $this->route('record')];
            while ($parent) {
                if (in_array((int) $parent, $visited, true)) {
                    $validator->errors()->add('parent_id', 'La categoría superior no puede ser ella misma ni una de sus descendientes.');

                    return;
                }
                $visited[] = (int) $parent;
                $parent = Category::find($parent)?->parent_id;
            }
        }];
    }

    public function messages(): array
    {
        return [
            'required' => 'Este campo es obligatorio.', 'present' => 'Este campo es obligatorio.',
            'required_if' => 'Indica la fecha para publicar el contenido.',
            'unique' => 'Este valor ya está en uso.', 'exists' => 'La opción seleccionada no es válida.',
            'max' => 'El valor supera el límite permitido (:max).', 'between' => 'El valor debe estar entre :min y :max.',
            'image' => 'Selecciona una imagen válida.', 'mimes' => 'El archivo debe ser de tipo :values.',
            'extensions' => 'La extensión debe ser :values.', 'slug.regex' => 'Usa letras minúsculas, números y guiones.',
        ];
    }
}
