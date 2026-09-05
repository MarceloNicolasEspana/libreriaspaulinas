import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Datos institucionales compartidos por HandleInertiaRequests.
 *
 * Evita repetir nombre, dirección o contacto dentro de los componentes: el
 * origen de esos valores es siempre config/paulinas.php.
 */
export function useInstitution() {
    const page = usePage();

    const institution = computed(() => page.props.institution);

    const fullAddress = computed(() => {
        const { street, commune, city, country } = institution.value.address;

        return [street, commune, city, country].filter(Boolean).join(', ');
    });

    return { institution, fullAddress };
}
