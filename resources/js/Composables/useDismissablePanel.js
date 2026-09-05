import { onBeforeUnmount, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

/**
 * Comportamiento común de los paneles que se abren sobre la página: el menú
 * móvil y el panel de filtros del catálogo.
 *
 * Mientras el panel está abierto bloquea el scroll del fondo, escucha Escape y
 * mueve el foco al panel para que el teclado y el lector de pantalla entren en
 * él.
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

    function onKeydown(event) {
        if (event.key === 'Escape') {
            close();
        }
    }

    watch(isOpen, async (open) => {
        document.body.classList.toggle('overflow-hidden', open);

        if (open) {
            document.addEventListener('keydown', onKeydown);
            // Un tick de espera: el panel todavía no está en el DOM.
            await Promise.resolve();
            panel.value?.focus();
        } else {
            document.removeEventListener('keydown', onKeydown);
        }
    });

    const stopNavigationListener = closeOnNavigate ? router.on('navigate', () => close()) : null;

    onBeforeUnmount(() => {
        document.removeEventListener('keydown', onKeydown);
        document.body.classList.remove('overflow-hidden');
        stopNavigationListener?.();
    });

    return { panel };
}
