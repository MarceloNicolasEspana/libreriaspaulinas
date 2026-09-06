<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    label: { type: String, required: true },
    href: { type: String, default: null },
    /** Contador opcional (carrito, favoritos). */
    count: { type: Number, default: 0 },
    /*
     * Accesos anunciados en el diseño cuya funcionalidad todavía no existe
     * (cuenta y favoritos). Se muestran deshabilitados en lugar de responder
     * con un botón mudo: así quedan fuera del orden de tabulación y el lector
     * de pantalla anuncia que aún no están disponibles.
     */
    unavailable: { type: Boolean, default: false },
});
</script>

<template>
    <component
        :is="href && !unavailable ? Link : 'button'"
        :href="unavailable ? undefined : href"
        :type="href && !unavailable ? undefined : 'button'"
        :disabled="unavailable || undefined"
        :aria-label="unavailable ? `${label} (próximamente)` : label"
        :title="unavailable ? `${label} (próximamente)` : label"
        class="relative inline-flex size-11 items-center justify-center rounded-full text-brand-800 transition-colors duration-150"
        :class="unavailable ? 'cursor-not-allowed opacity-40' : 'hover:bg-brand-50 active:bg-brand-100'"
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
