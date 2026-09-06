<?php

namespace App\Support\Admin;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class SaveRecord
{
    /** @param array<string, mixed> $data */
    public function handle(string $section, Model $record, array $data): Model
    {
        $createdFiles = [];
        $removedFiles = [];
        try {
            DB::transaction(function () use ($section, $record, $data, &$createdFiles, &$removedFiles): void {
                $attributes = Arr::except($data, ['author_ids', 'images', 'remove_image_ids', 'image_upload', 'file_upload', 'remove_image', 'remove_file']);
                $imageColumn = $section === 'recursos' ? 'thumbnail' : 'image';
                foreach (['image_upload' => [$imageColumn, 'public', 'admin/'.$section, 'remove_image'], 'file_upload' => ['file_path', 'local', 'resources', 'remove_file']] as $input => [$column, $disk, $folder, $remove]) {
                    if (! empty($data[$input]) || ! empty($data[$remove])) {
                        $oldPath = $record->exists ? $record->getAttribute($column) : null;
                        if ($oldPath) {
                            $removedFiles[] = [$disk, $oldPath];
                        }
                        $attributes[$column] = ! empty($data[$input]) ? $this->store($data[$input], $folder, $disk, $createdFiles) : null;
                    }
                }
                $record->fill($attributes)->save();
                if ($record instanceof Product) {
                    $record->authors()->sync(collect($data['author_ids'])->mapWithKeys(fn (int $id, int $index): array => [$id => ['sort_order' => $index]])->all());
                    foreach ($record->images()->whereIn('id', $data['remove_image_ids'] ?? [])->get() as $image) {
                        $removedFiles[] = ['public', $image->path];
                        $image->delete();
                    }
                    $order = (int) $record->images()->max('sort_order');
                    foreach ($data['images'] ?? [] as $image) {
                        $path = $this->store($image, 'admin/product-images', 'public', $createdFiles);
                        $record->images()->create(['path' => $path, 'alt' => $record->title, 'sort_order' => ++$order]);
                    }
                }
            });
        } catch (Throwable $exception) {
            foreach ($createdFiles as [$disk, $path]) {
                Storage::disk($disk)->delete($path);
            }
            throw $exception;
        }
        foreach ($removedFiles as [$disk, $path]) {
            if (str_starts_with($path, 'admin/') || ($disk === 'local' && str_starts_with($path, 'resources/'))) {
                Storage::disk($disk)->delete($path);
            }
        }
        Cache::forget('public-sitemap');

        return $record;
    }

    /** @param array<int, array{string, string}> $createdFiles */
    private function store(UploadedFile $file, string $folder, string $disk, array &$createdFiles): string
    {
        $path = $file->store($folder, $disk);
        if ($path === false) {
            throw new RuntimeException('No se pudo guardar el archivo.');
        }
        $createdFiles[] = [$disk, $path];

        return $path;
    }
}
