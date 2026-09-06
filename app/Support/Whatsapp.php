<?php

namespace App\Support;

use App\Models\Product;

class Whatsapp
{
    public static function availabilityUrl(Product $product): string
    {
        $number = preg_replace('/\D+/', '', (string) config('paulinas.contact.whatsapp'));
        $message = "Hola, quisiera consultar disponibilidad del libro \"{$product->title}\"";
        $message .= $product->isbn ? ", ISBN {$product->isbn}." : '.';

        return 'https://wa.me/'.$number.'?'.http_build_query(
            ['text' => $message],
            encoding_type: PHP_QUERY_RFC3986,
        );
    }
}
