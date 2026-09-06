<?php

namespace App\Models;

use Database\Factories\ResourceCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResourceCategory extends Model
{
    /** @use HasFactory<ResourceCategoryFactory> */
    use HasFactory;

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }
}
