<?php

namespace Database\Factories;

use App\Models\Collection;
use Database\Factories\Concerns\CyclesThroughPool;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Collection>
 */
class CollectionFactory extends Factory
{
    use CyclesThroughPool;

    protected $model = Collection::class;

    /** @var array<int, string> */
    private const NAMES = [
        'Palabra y Vida', 'Camino de Fe', 'Semillas', 'Pan Partido', 'Lámpara',
        'Testigos', 'Aula Abierta', 'Manantial', 'Cuadernos de Catequesis',
        'Voces del Evangelio', 'Pequeños Lectores', 'Itinerarios',
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
            'description' => "Colección {$name}: títulos de formato y enfoque comunes, "
                .'pensados para acompañar un mismo itinerario de lectura.',
        ];
    }
}
