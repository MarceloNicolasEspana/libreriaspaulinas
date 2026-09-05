<?php

namespace App\Support;

use NumberFormatter;

/**
 * Formatea montos monetarios según la moneda configurada en config/paulinas.php.
 *
 * Los montos se manejan como enteros en la unidad menor de la moneda. Para CLP,
 * que no usa decimales, esa unidad es el peso: 12990 => "$12.990".
 */
final class Money
{
    /**
     * Formatea un monto para mostrarlo al usuario.
     */
    public static function format(int $amount): string
    {
        $formatter = new NumberFormatter(self::locale(), NumberFormatter::CURRENCY);
        $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, self::fractionDigits());

        return $formatter->formatCurrency(
            $amount / (10 ** self::fractionDigits()),
            self::code(),
        );
    }

    public static function code(): string
    {
        return config('paulinas.currency.code');
    }

    public static function locale(): string
    {
        return config('paulinas.currency.locale');
    }

    public static function fractionDigits(): int
    {
        return (int) config('paulinas.currency.fraction_digits');
    }
}
