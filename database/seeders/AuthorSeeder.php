<?php

namespace Database\Seeders;

use App\Models\Author;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Autores y equipos de autoría.
 *
 * Los equipos reproducen la forma habitual de firmar el material pastoral. Las
 * personas son de demostración: los nombres no corresponden a autores reales.
 */
class AuthorSeeder extends Seeder
{
    /**
     * Nombre => biografía.
     *
     * @var array<string, string>
     */
    private const TEAMS = [
        'Equipo Bíblico Paulinas' => 'Reúne a biblistas y agentes pastorales que preparan '
            .'ediciones comentadas, guías de lectura y materiales de estudio de la Escritura.',
        'Equipo Pastoral Paulinas' => 'Elabora itinerarios de catequesis y subsidios para el '
            .'acompañamiento de comunidades.',
        'Equipo de Formación Paulinas' => 'Diseña programas de formación para catequistas, '
            .'docentes de religión y agentes pastorales.',
        'Comisión de Liturgia' => 'Prepara subsidios, cantorales y guías para la animación de '
            .'las celebraciones a lo largo del año litúrgico.',
        'Comunidad San Pablo' => 'Grupo de autores que trabaja textos de oración y '
            .'espiritualidad para la vida cotidiana.',
        'Equipo de Catequesis Familiar' => 'Produce materiales para el trabajo conjunto de '
            .'padres, hijos y catequistas.',
    ];

    /**
     * Nombre => línea de trabajo.
     *
     * @var array<string, string>
     */
    private const PEOPLE = [
        'Ana María Fuenzalida' => 'la catequesis familiar y la iniciación cristiana',
        'Carmen Larraín' => 'la pedagogía de la clase de religión',
        'Josefina Ossandón' => 'la literatura infantil de inspiración cristiana',
        'Marta Riquelme' => 'el acompañamiento espiritual de mujeres en comunidad',
        'Teresa Valdivieso' => 'la lectura orante de la Palabra',
        'Verónica Sepúlveda' => 'la formación de agentes pastorales',
        'Andrés Echeverría' => 'los estudios sobre el Nuevo Testamento',
        'Ignacio Benavente' => 'la teología pastoral y la doctrina social',
        'Javier Donoso' => 'la animación litúrgica y la música para la asamblea',
        'Manuel Guzmán' => 'la historia de la Iglesia en América Latina',
        'Rodrigo Cifuentes' => 'la pastoral juvenil y los itinerarios de discernimiento',
        'Tomás Peralta' => 'la espiritualidad del trabajo y la vida cotidiana',
    ];

    public function run(): void
    {
        foreach (self::TEAMS as $name => $biography) {
            $this->author($name, $biography);
        }

        foreach (self::PEOPLE as $name => $field) {
            $this->author(
                $name,
                "Autor de demostración. Su trabajo editorial se centra en {$field}, "
                    .'con títulos dirigidos a comunidades, familias y escuelas.',
            );
        }
    }

    private function author(string $name, string $biography): void
    {
        Author::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name, 'biography' => $biography],
        );
    }
}
