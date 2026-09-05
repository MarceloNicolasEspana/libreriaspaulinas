<script setup>
/**
 * Resultado vacío.
 *
 * Nunca se deja al visitante en un callejón sin salida: el bloque siempre
 * ofrece por dónde seguir, sea limpiando los filtros o entrando a una sección.
 */
defineProps({
    title: { type: String, required: true },
    description: { type: String, default: null },
});
</script>

<template>
    <div class="rounded-card border border-paper-200 bg-paper-50 px-6 py-14 text-center">
        <p class="font-serif text-lg font-semibold text-balance text-brand-900 sm:text-xl">
            {{ title }}
        </p>

        <p v-if="description" class="mx-auto mt-2 max-w-prose text-sm text-paper-600">
            {{ description }}
        </p>

        <div v-if="$slots.action" class="mt-6 flex flex-wrap justify-center gap-3">
            <slot name="action" />
        </div>

        <slot />
    </div>
</template>
