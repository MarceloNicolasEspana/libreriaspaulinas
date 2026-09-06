<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name') }}</title>

        {{--
            Datos estructurados de la página (ver App\Support\Seo).

            Se imprimen aquí y no en un componente Vue porque el compilador de
            plantillas descarta las etiquetas <script>. Además es lo que lee un
            rastreador al pedir la URL directamente, sin ejecutar JavaScript.

            Las banderas HEX de json_encode escapan <, >, & y las comillas, de
            modo que un título del catálogo no pueda cerrar la etiqueta.
        --}}
        @foreach (data_get($page, 'props.seo.schemas') ?? [] as $schema)
            <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
        @endforeach

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="h-full bg-white font-sans text-slate-800 antialiased">
        @inertia
    </body>
</html>
