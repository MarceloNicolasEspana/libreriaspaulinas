<?php

namespace Tests\Unit;

use App\Support\Money;
use Tests\TestCase;

class MoneyTest extends TestCase
{
    public function test_it_formats_amounts_as_chilean_pesos(): void
    {
        // El separador de miles en es-CL es un punto y no se muestran decimales.
        $this->assertSame('$12.990', $this->normalize(Money::format(12990)));
        $this->assertSame('$0', $this->normalize(Money::format(0)));
    }

    public function test_it_exposes_the_configured_currency(): void
    {
        $this->assertSame('CLP', Money::code());
        $this->assertSame(0, Money::fractionDigits());
    }

    /**
     * ICU intercala espacios no separables que dependen de la versión de la
     * librería; se normalizan para que la aserción sea estable.
     */
    private function normalize(string $value): string
    {
        return str_replace(["\u{00A0}", "\u{202F}", ' '], '', $value);
    }
}
