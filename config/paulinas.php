<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Datos institucionales
    |--------------------------------------------------------------------------
    |
    | Fuente única de verdad para los datos de la institución. Se comparten con
    | el frontend mediante el middleware HandleInertiaRequests, de modo que
    | nunca deban escribirse "a mano" dentro de un componente Vue.
    |
    */

    'legal_name' => 'PAULINAS Centro Pastoral de Comunicación Ltda.',

    'short_name' => 'Paulinas',

    'tagline' => 'Comunicar a Cristo en la cultura de hoy',

    'address' => [
        'street' => 'Inés de Suárez 7639',
        'commune' => 'La Florida',
        'city' => 'Santiago',
        'country' => 'Chile',
    ],

    'contact' => [
        'phone' => '+56 2 2255 0703',
        'whatsapp' => '+56935584641',
        'sales_email' => 'salaventas@paulinas.cl',
        'distribution_email' => 'distribuidora@paulinas.cl',
    ],

    'social' => [
        'facebook' => 'https://www.facebook.com/HermanasPaulinasChile',
        'instagram' => null,
        'youtube' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Localización comercial
    |--------------------------------------------------------------------------
    |
    | La tienda opera inicialmente en Chile. Los montos se almacenan como
    | enteros en la unidad menor de la moneda; el peso chileno no usa
    | decimales, por lo que "fraction_digits" es 0.
    |
    */

    'currency' => [
        'code' => 'CLP',
        'symbol' => '$',
        'locale' => 'es-CL',
        'fraction_digits' => 0,
    ],

];
