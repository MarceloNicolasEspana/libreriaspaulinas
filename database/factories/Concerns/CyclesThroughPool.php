<?php

namespace Database\Factories\Concerns;

/**
 * Reparte los valores de una lista fija sin repetirlos.
 *
 * Las factories del catálogo trabajan con listas cerradas de nombres para que
 * los datos se parezcan a los de una librería real. Como el slug de cada uno es
 * único en la base de datos, no pueden repetirse; y como una factory tiene que
 * poder crear tantos registros como pida quien la usa, tampoco puede agotarse:
 * al dar la vuelta a la lista, los valores se numeran.
 *
 * Cada clase que usa el trait lleva su propio contador.
 */
trait CyclesThroughPool
{
    private static int $poolSequence = 0;

    protected static function nextSequence(): int
    {
        return self::$poolSequence++;
    }

    /**
     * @param  array<int, string>  $pool
     */
    protected static function fromPool(array $pool, int $sequence): string
    {
        $value = $pool[$sequence % count($pool)];
        $lap = intdiv($sequence, count($pool));

        return $lap === 0 ? $value : "{$value} {$lap}";
    }
}
