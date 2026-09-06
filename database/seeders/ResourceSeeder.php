<?php

namespace Database\Seeders;

use App\Models\Resource;
use App\Models\ResourceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ResourceSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = 'resources/preparar-un-encuentro-de-catequesis.pdf';
        if (! Storage::disk('local')->exists($filePath)) {
            Storage::disk('local')->put($filePath, file_get_contents(__DIR__.'/fixtures/catequesis.pdf'));
        }

        $resource = Resource::firstOrCreate(['slug' => 'preparar-un-encuentro-de-catequesis'], [
            'file_path' => $filePath,
            'title' => 'Preparar un encuentro de catequesis',
            'description' => "Antes del encuentro, elige un objetivo sencillo y prepara un espacio acogedor.\n\nComienza escuchando cómo llega cada participante. Propón una lectura bíblica breve y deja tiempo para compartir preguntas.\n\nCierra invitando a elegir un gesto concreto para la semana. En el siguiente encuentro, retoma lo vivido y escucha al grupo.",
            'resource_category_id' => ResourceCategory::where('slug', 'catequistas')->firstOrFail()->id,
            'audience' => 'Catequistas y animadores de comunidad',
            'active' => true,
            'published_at' => '2026-09-01 12:00:00',
        ]);

        if ($resource->file_path === null) {
            $resource->update(['file_path' => $filePath]);
        }
    }
}
