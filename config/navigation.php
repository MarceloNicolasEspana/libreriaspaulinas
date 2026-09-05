<?php

/*
|--------------------------------------------------------------------------
| Estructura de navegación
|--------------------------------------------------------------------------
|
| Fuente única de verdad para el menú principal, el menú móvil y el footer.
| Header, MobileMenu y Footer consumen estos mismos datos vía Inertia, de modo
| que agregar una sección se hace en un solo lugar.
|
| Cuando existan las páginas reales, basta con apuntar 'href' a la ruta
| definitiva; los componentes no necesitan cambios.
|
*/

return [

    /*
     * Menú principal del header y del menú móvil.
     */
    'primary' => [
        ['label' => 'Libros', 'href' => '/libros'],
        ['label' => 'Biblias', 'href' => '/biblias'],
        ['label' => 'Catequesis', 'href' => '/catequesis'],
        ['label' => 'Niños', 'href' => '/ninos'],
        ['label' => 'Espiritualidad', 'href' => '/espiritualidad'],
        ['label' => 'Liturgia', 'href' => '/liturgia'],
        ['label' => 'Recursos', 'href' => '/recursos'],
        ['label' => 'Nuestras Librerías', 'href' => '/librerias'],
    ],

    /*
     * Columnas de enlaces del footer.
     */
    'footer' => [
        [
            'heading' => 'Librería',
            'links' => [
                ['label' => 'Libros', 'href' => '/libros'],
                ['label' => 'Biblias', 'href' => '/biblias'],
                ['label' => 'Catequesis', 'href' => '/catequesis'],
                ['label' => 'Niños', 'href' => '/ninos'],
                ['label' => 'Novedades', 'href' => '/novedades'],
            ],
        ],
        [
            'heading' => 'Paulinas',
            'links' => [
                ['label' => 'Quiénes somos', 'href' => '/quienes-somos'],
                ['label' => 'Nuestra misión', 'href' => '/nuestra-mision'],
                ['label' => 'Librerías', 'href' => '/librerias'],
                ['label' => 'Contacto', 'href' => '/contacto'],
            ],
        ],
        [
            'heading' => 'Recursos',
            'links' => [
                ['label' => 'Profesores', 'href' => '/recursos/profesores'],
                ['label' => 'Catequistas', 'href' => '/recursos/catequistas'],
                ['label' => 'Descargas', 'href' => '/recursos/descargas'],
                ['label' => 'Formación', 'href' => '/recursos/formacion'],
            ],
        ],
    ],

];
