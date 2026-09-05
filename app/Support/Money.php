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

        return $formatter->formatCurrency(self::toMajor($amount), self::code());
    }

    /**
     * Pasa un monto de la unidad mayor de la moneda a la menor: 12990.00 => 12990.
     *
     * Es la conversión que hacen el modelo Product al exponer el precio y el
     * catálogo al leer el rango de precios de la URL.
     */
    public static function toMinor(float|int|string $amount): int
    {
        return (int) round(((float) $amount) * (10 ** self::fractionDigits()));
    }

    /**
     * Pasa un monto de la unidad menor de la moneda a la mayor: 12990 => 12990.0.
     */
    public static function toMajor(int $amount): float
    {
        return $amount / (10 ** self::fractionDigits());
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
