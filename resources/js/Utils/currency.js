/**
 * Formateo de montos en la moneda configurada por el backend.
 *
 * Los montos llegan como enteros en la unidad menor de la moneda. El peso
 * chileno no usa decimales, de modo que 12990 se muestra como "$12.990".
 */
export function formatMoney(amount, currency) {
    if (amount === null || amount === undefined) {
        return '';
    }

    const digits = currency.fraction_digits ?? 0;

    return new Intl.NumberFormat(currency.locale, {
        style: 'currency',
        currency: currency.code,
        minimumFractionDigits: digits,
        maximumFractionDigits: digits,
    }).format(amount / 10 ** digits);
}
