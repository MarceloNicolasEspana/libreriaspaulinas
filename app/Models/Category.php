<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $guarded = [];

    /**
     * Las secciones se enlazan por slug: /categorias/biblias.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }

    /**
     * Secciones de primer nivel, las que forman el índice del catálogo.
     */
    public function scopeRoots(Builder $query): void
    {
        $query->whereNull('parent_id');
    }

    /**
     * Orden editorial: manda "sort_order" y el nombre resuelve los empates.
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Identificadores de esta sección y de todas las que cuelgan de ella.
     *
     * Una sección muestra también lo que hay en sus subsecciones: entrar en
     * "Biblias" y no ver los títulos de "Biblias de estudio" sería desconcertante.
     *
     * @return array<int, int>
     */
    public function descendantIds(): array
    {
        // Explícito: el proyecto tiene el lazy loading desactivado, y quien
        // llama no siempre trae el árbol precargado.
        $this->loadMissing('children');

        return $this->children
            ->flatMap(fn (self $child) => $child->descendantIds())
            ->prepend($this->id)
            ->all();
    }
}
