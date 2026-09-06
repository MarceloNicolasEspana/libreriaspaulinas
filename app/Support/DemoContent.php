<?php

namespace App\Support;

/**
 * Contenido de la portada que todavía no tiene modelo propio.
 *
 * TEMPORAL. El catálogo, las librerías y los recursos ya vienen de la base de
 * datos; aquí quedan solo las campañas del hero y los accesos destacados a la
 * sección de recursos, que se editan en código. Cuando tengan tabla propia,
 * este archivo se elimina.
 */
final class DemoContent
{
    /**
     * Campañas del hero.
     *
     * Se entregan todas y el componente omite las inactivas, de modo que
     * activar o pausar una campaña sea un cambio de dato y no de plantilla.
     * Se espera una sola campaña activa a la vez.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function heroBanners(): array
    {
        return [
            [
                'id' => 'mes-de-la-biblia',
                'active' => true,
                'image' => '/images/campanas/mes-de-la-biblia.svg',
                'imageAlt' => 'Ilustración de una Biblia abierta iluminada',
                'subtitle' => 'Mes de la Biblia',
                'title' => 'Vuelve a la Palabra que lo empezó todo',
                'description' => 'Descubre Biblias, comentarios, guías y recursos para profundizar '
                    .'en la Palabra de Dios.',
                'buttonLabel' => 'Ver colección',
                'buttonHref' => '/categorias/biblias',
                'align' => 'start',
            ],
            [
                /*
                 * Campaña fuera de temporada: queda configurada y no se muestra.
                 */
                'id' => 'adviento',
                'active' => false,
                'image' => null,
                'imageAlt' => '',
                'subtitle' => 'Adviento y Navidad',
                'title' => 'Prepara el camino en comunidad',
                'description' => 'Subsidios, coronas de Adviento y material para animar la espera.',
                'buttonLabel' => 'Ver materiales',
                'buttonHref' => '/liturgia',
                'align' => 'center',
            ],
        ];
    }

    /**
     * Accesos de la sección editorial para catequistas y profesores.
     *
     * @return array<int, array<string, string>>
     */
    public static function teachingResources(): array
    {
        return [
            [
                'title' => 'Planificaciones',
                'description' => 'Unidades y clases listas para adaptar al calendario escolar.',
                'href' => '/recursos?categoria=planificaciones',
                'icon' => 'clipboard',
            ],
            [
                'title' => 'Material descargable',
                'description' => 'Fichas, guías e imágenes para imprimir y trabajar en sala.',
                'href' => '/recursos?categoria=documentos',
                'icon' => 'download',
            ],
            [
                'title' => 'Recursos pastorales',
                'description' => 'Subsidios para animar celebraciones, retiros y encuentros.',
                'href' => '/recursos?categoria=pastoral',
                'icon' => 'heart',
            ],
            [
                'title' => 'Formación',
                'description' => 'Itinerarios para el crecimiento de agentes pastorales.',
                'href' => '/recursos?categoria=formacion',
                'icon' => 'graduation',
            ],
            [
                'title' => 'Programas de religión',
                'description' => 'Propuestas por nivel para el año escolar completo.',
                'href' => '/recursos?categoria=profesores-de-religion',
                'icon' => 'calendar',
            ],
            [
                'title' => 'Recursos bíblicos',
                'description' => 'Itinerarios, mapas y claves de lectura de la Escritura.',
                'href' => '/recursos?categoria=biblia',
                'icon' => 'book',
            ],
        ];
    }
}
