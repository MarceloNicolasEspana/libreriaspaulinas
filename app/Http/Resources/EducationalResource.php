<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin \App\Models\Resource */
class EducationalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 'title' => $this->title, 'description' => $this->description,
            'href' => route('resources.show', $this->resource, absolute: false),
            'category' => $this->category->name,
            'categoryHref' => route('resources.index', ['categoria' => $this->category->slug], absolute: false),
            'audience' => $this->audience,
            'published_at' => $this->published_at?->toDateString(),
            'date' => $this->published_at?->locale('es')->isoFormat('D [de] MMMM [de] YYYY'),
            'thumbnail' => $this->thumbnail ? Storage::disk('public')->url($this->thumbnail) : null,
            'file' => $this->when($request->routeIs('resources.show'), fn () => $this->hasDownload() ? basename($this->file_path) : null),
            'downloadHref' => $this->when($request->routeIs('resources.show'), fn () => $this->hasDownload() ? route('resources.download', $this->resource, absolute: false) : null),
        ];
    }
}
