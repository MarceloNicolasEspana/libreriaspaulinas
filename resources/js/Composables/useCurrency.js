import { usePage } from '@inertiajs/vue3';
import { formatMoney } from '@/Utils/currency';

/**
 * Formatea precios usando la moneda que comparte el backend (CLP por defecto).
 */
export function useCurrency() {
    const page = usePage();

    const format = (amount) => formatMoney(amount, page.props.currency);

    return { format };
}
