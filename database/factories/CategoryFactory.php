<?php

namespace Database\Factories;

use App\Models\Category;
use Database\Factories\Concerns\CyclesThroughPool;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    use CyclesThroughPool;

    protected $model = Category::class;

    /**
     * Secciones plausibles de una librería religiosa.
     *
     * @var array<int, string>
     */
    private const NAMES = [
        'Biblias', 'Catequesis', 'Niños', 'Oración', 'Espiritualidad', 'Liturgia',
        'Formación', 'Sacramentos', 'Santos', 'María', 'Teología', 'Regalos',
        'Familia', 'Jóvenes', 'Documentos de la Iglesia', 'Música', 'Pastoral juvenil',
        'Historia de la Iglesia', 'Retiros', 'Doctrina social', 'Educación religiosa',
        'Adviento y Navidad', 'Cuaresma y Pascua', 'Vida consagrada',
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = self::fromPool(self::NAMES, self::nextSequence());

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => "Títulos y materiales de la sección {$name}.",
            'parent_id' => null,
            'image' => null,
            'active' => true,
            'sort_order' => $this->faker->numberBetween(0, 99),
        ];
    }

    public function inactive(): static
    {
        return $this->state(['active' => false]);
    }

    /**
     * Subsección de la categoría indicada.
     */
    public function childOf(Category $parent): static
    {
        return $this->state(['parent_id' => $parent->id]);
    }
}
