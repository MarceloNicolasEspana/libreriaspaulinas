<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    label: { type: String, required: true },
    href: { type: String, default: null },
    /** Contador opcional (carrito, favoritos). */
    count: { type: Number, default: 0 },
});
</script>

<template>
    <component
        :is="href ? Link : 'button'"
        :href="href"
        :type="href ? undefined : 'button'"
        :aria-label="label"
        :title="label"
        class="relative inline-flex size-11 items-center justify-center rounded-full text-brand-800 transition-colors duration-150 hover:bg-brand-50 active:bg-brand-100"
    >
        <slot />

        <span
            v-if="count > 0"
            class="absolute top-1 right-0.5 min-w-4.5 rounded-full bg-accent-600 px-1 text-[0.65rem] leading-4.5 font-semibold text-white"
        >
            {{ count }}
        </span>
    </component>
</template>
