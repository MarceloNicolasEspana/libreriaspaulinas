import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Estructura de navegación compartida por HandleInertiaRequests.
 *
 * Header, MobileMenu y Footer leen desde aquí para no duplicar el listado de
 * secciones; su origen es config/navigation.php.
 */
export function useNavigation() {
    const page = usePage();

    const primary = computed(() => page.props.navigation.primary);
    const footerColumns = computed(() => page.props.navigation.footer);

    return { primary, footerColumns };
}
