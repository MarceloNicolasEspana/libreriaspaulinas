<?php

namespace Database\Seeders;

use App\Models\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Colecciones editoriales de demostración.
 *
 * La colección agrupa títulos por serie y formato, no por tema: esa es la
 * diferencia con la categoría.
 */
class CollectionSeeder extends Seeder
{
    /**
     * Nombre => descripción.
     *
     * @var array<string, string>
     */
    private const COLLECTIONS = [
        'Palabra y Vida' => 'Comentarios y guías de lectura de los libros de la Escritura.',
        'Camino de Fe' => 'Itinerarios de iniciación cristiana para grupos y familias.',
        'Semillas' => 'Libros ilustrados para los primeros lectores.',
        'Pan Partido' => 'Textos breves de espiritualidad para la oración de cada día.',
        'Lámpara' => 'Ensayos de teología y pensamiento cristiano contemporáneo.',
        'Testigos' => 'Biografías de santos y de figuras de la Iglesia.',
        'Aula Abierta' => 'Recursos para la clase de religión y la formación docente.',
        'Cuadernos de Catequesis' => 'Fichas y subsidios para el trabajo semanal del catequista.',
    ];

    public function run(): void
    {
        foreach (self::COLLECTIONS as $name => $description) {
            Collection::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => $description],
            );
        }
    }
}
