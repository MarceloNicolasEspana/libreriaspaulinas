<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Secciones del catálogo.
 *
 * Los nombres de primer nivel son los que el sitio ya anuncia en el menú y en
 * la portada. Las subsecciones son propuestas de demostración: sirven para
 * ejercitar la jerarquía y se ajustarán al árbol definitivo.
 */
class CategorySeeder extends Seeder
{
    /**
     * Sección => subsecciones.
     *
     * @var array<string, array<int, string>>
     */
    private const TREE = [
        'Biblias' => ['Biblias de estudio', 'Biblias para niños', 'Nuevo Testamento'],
        'Catequesis' => ['Catequesis familiar', 'Primera Comunión', 'Confirmación'],
        'Niños' => ['Relatos bíblicos', 'Actividades y manualidades'],
        'Oración' => ['Oración diaria', 'Salmos'],
        'Espiritualidad' => ['Retiros', 'Acompañamiento espiritual'],
        'Liturgia' => ['Cantoral', 'Subsidios litúrgicos'],
        'Formación' => ['Agentes pastorales', 'Educación religiosa escolar'],
        'Sacramentos' => [],
        'Santos' => [],
        'María' => [],
        'Teología' => ['Teología bíblica', 'Doctrina social'],
        'Regalos' => ['Tarjetería', 'Artículos religiosos'],
    ];

    public function run(): void
    {
        $sortOrder = 0;

        foreach (self::TREE as $section => $subsections) {
            $parent = $this->category($section, $sortOrder += 10);

            $childOrder = 0;

            foreach ($subsections as $subsection) {
                $this->category($subsection, $childOrder += 10, $parent);
            }
        }
    }

    private function category(string $name, int $sortOrder, ?Category $parent = null): Category
    {
        return Category::firstOrCreate(
            ['slug' => Str::slug($name)],
            [
                'name' => $name,
                'description' => $parent
                    ? "Títulos de {$name}, dentro de {$parent->name}."
                    : "Todo el material de la sección {$name}.",
                'parent_id' => $parent?->id,
                'active' => true,
                'sort_order' => $sortOrder,
            ],
        );
    }
}
