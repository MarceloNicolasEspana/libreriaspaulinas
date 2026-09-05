<?php

namespace App\Support;

/**
 * Contenido de demostración de la portada que todavía no tiene modelo propio.
 *
 * TEMPORAL. Los productos y las secciones del catálogo ya vienen de la base de
 * datos; aquí quedan solo las campañas del hero, los accesos a recursos y las
 * librerías, que aún no tienen tabla. Cuando la tengan, este archivo se elimina.
 *
 * Los datos que quedan son ficticios: las direcciones, horarios y teléfonos de
 * las librerías son marcadores de posición y no representan sucursales reales.
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
                'buttonHref' => '/biblias',
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
                'href' => '/recursos/planificaciones',
                'icon' => 'clipboard',
            ],
            [
                'title' => 'Material descargable',
                'description' => 'Fichas, guías e imágenes para imprimir y trabajar en sala.',
                'href' => '/recursos/descargables',
                'icon' => 'download',
            ],
            [
                'title' => 'Recursos pastorales',
                'description' => 'Subsidios para animar celebraciones, retiros y encuentros.',
                'href' => '/recursos/pastorales',
                'icon' => 'heart',
            ],
            [
                'title' => 'Formación',
                'description' => 'Itinerarios para el crecimiento de agentes pastorales.',
                'href' => '/recursos/formacion',
                'icon' => 'graduation',
            ],
            [
                'title' => 'Programas de religión',
                'description' => 'Propuestas por nivel para el año escolar completo.',
                'href' => '/recursos/programas-de-religion',
                'icon' => 'calendar',
            ],
            [
                'title' => 'Recursos bíblicos',
                'description' => 'Itinerarios, mapas y claves de lectura de la Escritura.',
                'href' => '/recursos/biblicos',
                'icon' => 'book',
            ],
        ];
    }

    /**
     * Librerías. Datos de referencia: se reemplazan por la información oficial.
     *
     * @return array<int, array<string, string>>
     */
    public static function branches(): array
    {
        return [
            [
                'name' => 'Paulinas Santiago Centro',
                'city' => 'Santiago',
                'address' => 'Dirección por confirmar 1234, Santiago Centro',
                'hours' => 'Lun a Vie 10:00–19:00 · Sáb 10:00–14:00',
                'phone' => '+56 2 2000 0000',
                'whatsapp' => '+56 9 0000 0000',
                'href' => '/librerias/santiago-centro',
            ],
            [
                'name' => 'Paulinas La Florida',
                'city' => 'Santiago',
                'address' => 'Dirección por confirmar 7639, La Florida',
                'hours' => 'Lun a Vie 09:30–18:30 · Sáb 10:00–14:00',
                'phone' => '+56 2 2000 0001',
                'whatsapp' => '+56 9 0000 0001',
                'href' => '/librerias/la-florida',
            ],
            [
                'name' => 'Paulinas Valparaíso',
                'city' => 'Valparaíso',
                'address' => 'Dirección por confirmar 456, Valparaíso',
                'hours' => 'Lun a Vie 10:00–18:30 · Sáb 10:00–13:30',
                'phone' => '+56 32 200 0000',
                'whatsapp' => '+56 9 0000 0002',
                'href' => '/librerias/valparaiso',
            ],
        ];
    }

    /**
     * Rutas que la portada enlaza y que todavía no tienen página propia.
     *
     * Sirve para registrarlas como marcador de posición y para reconstruir su
     * título, de modo que ningún enlace de la Home termine en un 404.
     *
     * @return array<string, string> ruta => título
     */
    public static function placeholderLinks(): array
    {
        return collect(self::teachingResources())
            ->mapWithKeys(fn (array $resource) => [$resource['href'] => $resource['title']])
            ->merge(
                collect(self::branches())
                    ->mapWithKeys(fn (array $branch) => [$branch['href'] => $branch['name']])
            )
            ->all();
    }
}
