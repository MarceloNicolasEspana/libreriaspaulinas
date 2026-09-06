<?php

namespace App\Models;

use Database\Factories\ResourceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Resource extends Model
{
    /** @use HasFactory<ResourceFactory> */
    use HasFactory;

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['active' => 'boolean', 'published_at' => 'datetime'];
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('active', true)->where('published_at', '<=', now());
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ResourceCategory::class, 'resource_category_id');
    }

    public function hasDownload(): bool
    {
        return is_string($this->file_path) && str_starts_with($this->file_path, 'resources/')
        && ! str_contains($this->file_path, '..') && ! str_contains($this->file_path, '\\')
        && Storage::disk('local')->exists($this->file_path);
    }
}
