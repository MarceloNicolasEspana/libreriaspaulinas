<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
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

const page = usePage();
const cartHref = computed(() => page.props.cartSummary?.href ?? '/carrito');

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
                                <ChevronRightIcon class="size-4.5 text-paper-500" aria-hidden="true" />
                            </Link>
                        </li>
                    </ul>
                </nav>

                <div class="grid grid-cols-3 gap-1 border-t border-brand-100 p-3">
                    <!--
                        La cuenta y los favoritos están anunciados en el diseño
                        pero todavía no existen: se muestran deshabilitados en
                        vez de responder con un botón mudo.
                    -->
                    <button
                        v-for="action in [
                            { label: 'Mi cuenta', icon: UserIcon },
                            { label: 'Favoritos', icon: HeartIcon },
                        ]"
                        :key="action.label"
                        type="button"
                        disabled
                        :aria-label="`${action.label} (próximamente)`"
                        class="flex min-h-16 cursor-not-allowed flex-col items-center justify-center gap-1.5 rounded-control text-xs font-medium text-brand-800 opacity-40"
                    >
                        <component :is="action.icon" class="size-5.5" aria-hidden="true" />
                        {{ action.label }}
                    </button>

                    <Link
                        :href="cartHref"
                        class="flex min-h-16 flex-col items-center justify-center gap-1.5 rounded-control text-xs font-medium text-brand-800 transition-colors hover:bg-brand-50 active:bg-brand-100"
                    >
                        <CartIcon class="size-5.5" aria-hidden="true" />
                        Carrito
                    </Link>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
