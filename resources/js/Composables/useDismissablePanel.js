import { onBeforeUnmount, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

/**
 * Selector de lo que puede recibir el foco dentro del panel. Se excluye lo que
 * está deshabilitado y lo que se sacó del orden de tabulación a propósito.
 */
const FOCUSABLE =
    'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

/**
 * Comportamiento común de los paneles que se abren sobre la página: el menú
 * móvil y el panel de filtros del catálogo.
 *
 * Mientras el panel está abierto bloquea el scroll del fondo, escucha Escape,
 * mueve el foco al panel y lo retiene dentro. Ambos paneles se anuncian con
 * aria-modal, y un lector de pantalla que llegue con el tabulador al contenido
 * de atrás desmentiría esa promesa. Al cerrar, el foco vuelve al botón que
 * abrió el panel, para no dejarlo al principio del documento.
 *
 * @param {() => boolean} isOpen  Estado de apertura.
 * @param {() => void} close  Qué hacer para cerrar.
 * @param {{ closeOnNavigate?: boolean }} options
 *   closeOnNavigate cierra el panel al cambiar de página, para que no quede
 *   abierto sobre la siguiente. Lo quiere el menú de navegación, pero no el de
 *   filtros: ahí cada filtro es una navegación y cerrar el panel obligaría a
 *   reabrirlo para poner el siguiente.
 * @returns {{ panel: import('vue').Ref }} Ref que debe recibir el elemento del panel.
 */
export function useDismissablePanel(isOpen, close, { closeOnNavigate = true } = {}) {
    const panel = ref(null);

    // Quién tenía el foco antes de abrir, para devolvérselo al cerrar.
    let previouslyFocused = null;

    function focusableItems() {
        return Array.from(panel.value?.querySelectorAll(FOCUSABLE) ?? []).filter(
            (element) => element.offsetParent !== null || element === document.activeElement,
        );
    }

    /**
     * Mantiene el tabulador dentro del panel: desde el último elemento pasa al
     * primero, y desde el primero con Shift vuelve al último.
     */
    function trapTab(event) {
        const items = focusableItems();

        if (items.length === 0) {
            event.preventDefault();
            panel.value?.focus();

            return;
        }

        const first = items[0];
        const last = items[items.length - 1];
        const active = document.activeElement;

        if (event.shiftKey && (active === first || active === panel.value)) {
            event.preventDefault();
            last.focus();

            return;
        }

        if (!event.shiftKey && active === last) {
            event.preventDefault();
            first.focus();
        }
    }

    function onKeydown(event) {
        if (event.key === 'Escape') {
            close();

            return;
        }

        if (event.key === 'Tab') {
            trapTab(event);
        }
    }

    watch(isOpen, async (open) => {
        document.body.classList.toggle('overflow-hidden', open);

        if (open) {
            previouslyFocused = document.activeElement;
            document.addEventListener('keydown', onKeydown);
            // Un tick de espera: el panel todavía no está en el DOM.
            await Promise.resolve();
            panel.value?.focus();

            return;
        }

        document.removeEventListener('keydown', onKeydown);
        previouslyFocused?.focus?.();
        previouslyFocused = null;
    });

    const stopNavigationListener = closeOnNavigate ? router.on('navigate', () => close()) : null;

    onBeforeUnmount(() => {
        document.removeEventListener('keydown', onKeydown);
        document.body.classList.remove('overflow-hidden');
        stopNavigationListener?.();
    });

    return { panel };
}
