<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import ChevronRightIcon from '@/Components/Icons/ChevronRightIcon.vue';

const props = defineProps({
    name: { type: String, required: true },
    href: { type: String, required: true },
    /** Cantidad de títulos de la sección; se omite si no se conoce. */
    count: { type: Number, default: null },
});

/*
 * Cada tarjeta lleva un canto de color, como el lomo de un libro en la
 * estantería. El tono se deriva del nombre para que sea estable.
 */
const spines = ['bg-brand-700', 'bg-accent-700', 'bg-gold-600', 'bg-brand-500'];

const spine = computed(() => {
    let hash = 0;

    for (const character of props.name) {
        hash = (hash * 31 + character.codePointAt(0)) % 9973;
    }

    return spines[hash % spines.length];
});
</script>

<template>
    <Link
        :href="href"
        class="group relative flex h-full items-center gap-3 overflow-hidden rounded-card border border-paper-200 bg-white py-4 pr-4 pl-5 shadow-subtle transition-all duration-150 hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-card sm:py-5 sm:pl-6"
    >
        <span
            class="absolute inset-y-0 left-0 w-1 transition-all duration-150 group-hover:w-1.5"
            :class="spine"
            aria-hidden="true"
        />

        <span class="min-w-0">
            <span class="block font-serif text-base leading-snug font-semibold text-brand-900 sm:text-lg">
                {{ name }}
            </span>
            <span v-if="count" class="mt-0.5 block text-xs text-paper-600">{{ count }} títulos</span>
        </span>

        <ChevronRightIcon
            class="ml-auto size-4 shrink-0 text-accent-600 opacity-50 transition-all duration-150 group-hover:translate-x-0.5 group-hover:opacity-100"
        />
    </Link>
</template>
