<script setup>
import { Link } from '@inertiajs/vue3';
import AppLogo from '@/Components/AppLogo.vue';
import SearchBar from '@/Components/SearchBar.vue';
import CartIcon from '@/Components/Icons/CartIcon.vue';
import ChevronRightIcon from '@/Components/Icons/ChevronRightIcon.vue';
import CloseIcon from '@/Components/Icons/CloseIcon.vue';
import HeartIcon from '@/Components/Icons/HeartIcon.vue';
import UserIcon from '@/Components/Icons/UserIcon.vue';
import { useDismissablePanel } from '@/Composables/useDismissablePanel';
import { useNavigation } from '@/Composables/useNavigation';

const props = defineProps({
    open: { type: Boolean, default: false },
});

const emit = defineEmits(['close']);

const { primary } = useNavigation();

function close() {
    emit('close');
}

// Bloqueo del scroll de fondo, Escape, foco y cierre al navegar.
const { panel } = useDismissablePanel(() => props.open, close);
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
            enter-from-class="translate-x-full"
            leave-to-class="translate-x-full"
        >
            <div
                v-if="open"
                ref="panel"
                tabindex="-1"
                role="dialog"
                aria-modal="true"
                aria-label="Menú de navegación"
                class="fixed inset-y-0 right-0 z-50 flex w-[min(22rem,88vw)] flex-col bg-white shadow-raised focus:outline-none lg:hidden"
            >
                <div class="flex items-center justify-between border-b border-brand-100 px-5 py-4">
                    <AppLogo />
                    <button
                        type="button"
                        aria-label="Cerrar menú"
                        class="inline-flex size-11 items-center justify-center rounded-full text-brand-800 transition-colors hover:bg-brand-50 active:bg-brand-100"
                        @click="close"
                    >
                        <CloseIcon class="size-6" />
                    </button>
                </div>

                <div class="border-b border-brand-100 px-5 py-4">
                    <SearchBar size="compact" @submitted="close" />
                </div>

                <nav class="flex-1 overflow-y-auto overscroll-contain px-2 py-3" aria-label="Categorías">
                    <ul>
                        <li v-for="item in primary" :key="item.href">
                            <!-- Alto mínimo de 56px: cómodo para el pulgar. -->
                            <Link
                                :href="item.href"
                                class="flex min-h-14 items-center justify-between gap-3 rounded-control px-3 font-medium text-brand-900 transition-colors hover:bg-brand-50 active:bg-brand-100"
                            >
                                {{ item.label }}
                                <ChevronRightIcon class="size-4.5 text-paper-400" />
                            </Link>
                        </li>
                    </ul>
                </nav>

                <div class="grid grid-cols-3 gap-1 border-t border-brand-100 p-3">
                    <button
                        v-for="action in [
                            { label: 'Mi cuenta', icon: UserIcon },
                            { label: 'Favoritos', icon: HeartIcon },
                            { label: 'Carrito', icon: CartIcon },
                        ]"
                        :key="action.label"
                        type="button"
                        class="flex min-h-16 flex-col items-center justify-center gap-1.5 rounded-control text-xs font-medium text-brand-800 transition-colors hover:bg-brand-50 active:bg-brand-100"
                    >
                        <component :is="action.icon" class="size-5.5" />
                        {{ action.label }}
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
