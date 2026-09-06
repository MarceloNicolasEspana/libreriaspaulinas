<?php

namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Post */
class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 'title' => $this->title, 'excerpt' => $this->excerpt,
            'content' => $this->when($request->routeIs('posts.show'), fn () => $this->content),
            'href' => route('posts.show', $this->resource, absolute: false),
            'image' => $this->image ? Storage::disk('public')->url($this->image) : null,
            'published_at' => $this->published_at?->toDateString(),
            'date' => $this->published_at?->locale('es')->isoFormat('D [de] MMMM [de] YYYY'),
        ];
    }
}
