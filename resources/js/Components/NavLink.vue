<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    href: { type: String, required: true },
});

const page = usePage();

// Marca activa también las subsecciones (/recursos/profesores dentro de /recursos).
const isActive = computed(() => {
    const path = page.url.split('?')[0];

    return path === props.href || path.startsWith(`${props.href}/`);
});
</script>

<template>
    <Link
        :href="href"
        :aria-current="isActive ? 'page' : undefined"
        class="relative inline-flex items-center px-3 py-3.5 text-sm font-medium whitespace-nowrap transition-colors duration-150"
        :class="isActive ? 'text-accent-700' : 'text-brand-800 hover:text-accent-700'"
    >
        <slot />
        <span
            class="absolute inset-x-3 bottom-0 h-0.5 rounded-full bg-accent-600 transition-opacity duration-150"
            :class="isActive ? 'opacity-100' : 'opacity-0'"
        />
    </Link>
</template>
