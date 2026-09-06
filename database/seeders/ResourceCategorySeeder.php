<?php

namespace Database\Seeders;

use App\Models\ResourceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ResourceCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Catequistas', 'Profesores de religión', 'Planificaciones', 'Biblia', 'Pastoral', 'Formación', 'Documentos'] as $name) {
            ResourceCategory::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
        }
    }
}
