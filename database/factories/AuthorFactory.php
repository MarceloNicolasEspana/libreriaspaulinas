<?php

namespace Database\Factories;

use App\Models\Author;
use Database\Factories\Concerns\CyclesThroughPool;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Author>
 */
class AuthorFactory extends Factory
{
    use CyclesThroughPool;

    protected $model = Author::class;

    /** @var array<int, string> */
    private const TEAM_NAMES = [
        'Equipo Bíblico Paulinas', 'Equipo Pastoral Paulinas', 'Equipo de Formación Paulinas',
        'Comisión de Liturgia', 'Comunidad San Pablo', 'Equipo de Catequesis Familiar',
    ];

    /** @var array<int, string> */
    private const GIVEN_NAMES = [
        'Ana María', 'Carmen', 'Josefina', 'Marta', 'Teresa', 'Verónica', 'Pilar',
        'Andrés', 'Ignacio', 'Javier', 'Manuel', 'Rodrigo', 'Tomás', 'Vicente',
    ];

    /** @var array<int, string> */
    private const FAMILY_NAMES = [
        'Aguirre', 'Benavente', 'Cifuentes', 'Donoso', 'Echeverría', 'Fuenzalida',
        'Guzmán', 'Herrera', 'Larraín', 'Moreno', 'Ossandón', 'Peralta', 'Quiroga',
        'Riquelme', 'Sepúlveda', 'Valdivieso',
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = self::fromPool(self::names(), self::nextSequence());

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'biography' => "{$name} acompaña procesos de formación en comunidades y "
                .'escuelas, y ha publicado material de catequesis y espiritualidad.',
            'image' => null,
        ];
    }

    /**
     * Autoría colectiva: equipos y comisiones, habituales en el material pastoral.
     */
    public function team(): static
    {
        return $this->state(function (): array {
            $name = self::fromPool(self::TEAM_NAMES, self::nextSequence());

            return [
                'name' => $name,
                'slug' => Str::slug($name),
                'biography' => "{$name} reúne a especialistas que preparan materiales para la "
                    .'catequesis, la liturgia y la formación de agentes pastorales.',
            ];
        });
    }

    /**
     * Nombre y apellido combinados: 224 nombres distintos.
     *
     * @return array<int, string>
     */
    private static function names(): array
    {
        static $names = null;

        return $names ??= collect(self::GIVEN_NAMES)
            ->crossJoin(self::FAMILY_NAMES)
            ->map(fn (array $pair) => implode(' ', $pair))
            ->all();
    }
}
