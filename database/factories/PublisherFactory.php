<?php

namespace Database\Factories;

use App\Models\Publisher;
use Database\Factories\Concerns\CyclesThroughPool;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Publisher>
 */
class PublisherFactory extends Factory
{
    use CyclesThroughPool;

    protected $model = Publisher::class;

    /**
     * Sellos propios de la casa más nombres de demostración: no se atribuyen
     * títulos inventados a editoriales reales de terceros.
     *
     * @var array<int, string>
     */
    private const NAMES = [
        'Ediciones Paulinas', 'Paulinas Chile', 'Editorial Camino Abierto',
        'Ediciones Fuente Viva', 'Editorial Surco', 'Ediciones Betania Austral',
        'Editorial Cenáculo', 'Ediciones Pan y Mesa', 'Editorial Aurora del Sur',
        'Ediciones Tabor', 'Editorial Nazaret Austral', 'Ediciones Emaús',
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
            'description' => "{$name} publica libros de espiritualidad, catequesis y "
                .'formación para comunidades y colegios.',
        ];
    }
}
