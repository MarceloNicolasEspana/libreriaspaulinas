<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import BookCover from '@/Components/BookCover.vue';
import ChevronRightIcon from '@/Components/Icons/ChevronRightIcon.vue';
import { useCurrency } from '@/Composables/useCurrency';

const props = defineProps({
    title: { type: String, required: true },
    author: { type: String, default: null },
    category: { type: String, default: null },
    categoryHref: { type: String, default: null },
    /** Monto entero en la unidad menor de la moneda. */
    price: { type: Number, required: true },
    availability: {
        type: String,
        default: 'in_stock',
        validator: (value) => ['in_stock', 'low_stock', 'out_of_stock'].includes(value),
    },
    cover: { type: String, default: null },
    href: { type: String, required: true },
});

const { format } = useCurrency();

// El texto y el color del estado viven juntos: son la misma decisión.
const states = {
    in_stock: { label: 'Disponible', dot: 'bg-success-600', text: 'text-success-700' },
    low_stock: { label: 'Últimas unidades', dot: 'bg-warning-600', text: 'text-warning-700' },
    out_of_stock: { label: 'Bajo pedido', dot: 'bg-paper-400', text: 'text-paper-600' },
};

const state = computed(() => states[props.availability] ?? states.in_stock);
</script>

<template>
    <article class="group flex h-full flex-col">
        <Link :href="href" class="block" tabindex="-1" aria-hidden="true">
            <BookCover
                :title="title"
                :author="author"
                :category="category"
                :image="cover"
                class="transition-transform duration-200 group-hover:-translate-y-1"
            />
        </Link>

        <div class="mt-4 flex flex-1 flex-col">
            <Link
                v-if="category && categoryHref"
                :href="categoryHref"
                class="text-[0.65rem] font-semibold tracking-[0.14em] text-accent-700 uppercase transition-colors hover:text-accent-800"
            >
                {{ category }}
            </Link>

            <h3 class="mt-1.5 font-serif text-base leading-snug font-semibold sm:text-lg">
                <Link :href="href" class="line-clamp-2 transition-colors hover:text-accent-700">
                    {{ title }}
                </Link>
            </h3>

            <p v-if="author" class="mt-1 line-clamp-1 text-sm text-paper-600">{{ author }}</p>

            <!-- El bloque de precio queda alineado abajo aunque el título ocupe una o dos líneas. -->
            <div class="mt-auto pt-3">
                <p class="text-lg font-semibold text-brand-900">{{ format(price) }}</p>

                <p class="mt-1 flex items-center gap-1.5 text-xs" :class="state.text">
                    <span class="size-1.5 shrink-0 rounded-full" :class="state.dot" aria-hidden="true" />
                    {{ state.label }}
                </p>

                <Link
                    :href="href"
                    class="mt-3 inline-flex items-center gap-1 text-sm font-medium text-brand-800 transition-colors hover:text-accent-700"
                >
                    Ver detalle
                    <ChevronRightIcon class="size-3.5 transition-transform duration-150 group-hover:translate-x-0.5" />
                </Link>
            </div>
        </div>
    </article>
</template>
