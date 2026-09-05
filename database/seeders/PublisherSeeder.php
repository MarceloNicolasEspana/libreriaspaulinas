<?php

namespace Database\Seeders;

use App\Models\Publisher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Editoriales.
 *
 * Solo los dos primeros sellos son los de la casa. El resto son nombres de
 * demostración: no se atribuyen títulos inventados a editoriales reales de
 * terceros.
 */
class PublisherSeeder extends Seeder
{
    /**
     * Nombre => descripción.
     *
     * @var array<string, string>
     */
    private const PUBLISHERS = [
        'Ediciones Paulinas' => 'Sello editorial de las Hijas de San Pablo, dedicado a la '
            .'evangelización a través del libro y los medios de comunicación.',
        'Paulinas Chile' => 'Producción local de materiales pastorales y catequéticos para '
            .'comunidades y colegios del país.',
        'Editorial Camino Abierto' => 'Editorial de demostración centrada en espiritualidad y '
            .'acompañamiento de procesos personales.',
        'Ediciones Fuente Viva' => 'Editorial de demostración especializada en estudio bíblico '
            .'y materiales de lectura orante.',
        'Editorial Surco' => 'Editorial de demostración con catálogo de formación para agentes '
            .'pastorales y docentes de religión.',
        'Ediciones Betania Austral' => 'Editorial de demostración dedicada a la literatura '
            .'infantil de inspiración cristiana.',
    ];

    public function run(): void
    {
        foreach (self::PUBLISHERS as $name => $description) {
            Publisher::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => $description],
            );
        }
    }
}
