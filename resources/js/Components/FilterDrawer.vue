<script setup>
import CloseIcon from '@/Components/Icons/CloseIcon.vue';
import { useDismissablePanel } from '@/Composables/useDismissablePanel';

/**
 * Panel de filtros en móvil.
 *
 * En pantallas anchas la barra de filtros vive a un costado y siempre está a la
 * vista; en móvil no cabe, así que se abre desde el botón "Filtros" de la barra
 * de herramientas.
 *
 * No se cierra al navegar: cada filtro que se marca es una navegación, y
 * cerrarlo obligaría a reabrir el panel para poner el siguiente. Se cierra con
 * el botón, con Escape, tocando el fondo o con el botón de ver resultados.
 */
const props = defineProps({
    open: { type: Boolean, default: false },
    title: { type: String, default: 'Filtros' },
});

const emit = defineEmits(['close']);

function close() {
    emit('close');
}

const { panel } = useDismissablePanel(() => props.open, close, { closeOnNavigate: false });
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200"
            leave-active-class="transition-opacity duration-150"
            enter-from-class="opacity-0"
            leave-to-class="opacity-0"
        >
            <div v-if="open" class="fixed inset-0 z-50 bg-brand-950/40 lg:hidden" @click="close" />
        </Transition>

        <Transition
            enter-active-class="transition-transform duration-250 ease-out"
            leave-active-class="transition-transform duration-200 ease-in"
            enter-from-class="-translate-x-full"
            leave-to-class="-translate-x-full"
        >
            <div
                v-if="open"
                ref="panel"
                tabindex="-1"
                role="dialog"
                aria-modal="true"
                :aria-label="title"
                class="fixed inset-y-0 left-0 z-50 flex w-[min(22rem,88vw)] flex-col bg-white shadow-raised focus:outline-none lg:hidden"
            >
                <div class="flex items-center justify-between border-b border-brand-100 px-5 py-4">
                    <h2 class="font-serif text-lg font-semibold text-brand-900">{{ title }}</h2>

                    <button
                        type="button"
                        aria-label="Cerrar filtros"
                        class="inline-flex size-11 items-center justify-center rounded-full text-brand-800 transition-colors hover:bg-brand-50 active:bg-brand-100"
                        @click="close"
                    >
                        <CloseIcon class="size-6" />
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto overscroll-contain px-5 py-5">
                    <slot />
                </div>

                <div v-if="$slots.footer" class="border-t border-brand-100 p-4">
                    <slot name="footer" />
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
