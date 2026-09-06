<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        Post::firstOrCreate(['slug' => 'leer-y-compartir-en-comunidad'], [
            'title' => 'Leer y compartir en comunidad',
            'excerpt' => 'Una lectura puede abrir una conversación. Algunas ideas para hacer del encuentro con los libros una experiencia compartida.',
            'content' => "Leer en comunidad comienza con una invitación sencilla: reservar un momento para escuchar y dejarse sorprender por las palabras de otra persona.\n\nPara comenzar, elijan un texto breve. Una persona puede leerlo en voz alta y luego dar paso a un momento de silencio. No es necesario encontrar una respuesta única: cada participante puede compartir la frase que más le llamó la atención.\n\nUna pregunta abierta ayuda a conectar la lectura con la vida cotidiana: ¿qué nos invita a mirar de otra manera?\n\nAl finalizar, acuerden el próximo encuentro y una nueva lectura. La continuidad permite que el grupo construya confianza y encuentre su propio ritmo.",
            'active' => true,
            'published_at' => '2026-09-01 12:00:00',
        ]);
    }
}
