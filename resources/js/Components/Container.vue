<script setup>
import { computed } from 'vue';

const props = defineProps({
    /** Ancho máximo del contenido. */
    size: {
        type: String,
        default: 'default',
        validator: (value) => ['narrow', 'default', 'wide', 'full'].includes(value),
    },
    /** Etiqueta HTML a renderizar (section, header, footer...). */
    as: { type: String, default: 'div' },
});

const widths = {
    narrow: 'max-w-3xl',
    default: 'max-w-6xl',
    wide: 'max-w-7xl',
    full: 'max-w-none',
};

const width = computed(() => widths[props.size]);
</script>

<template>
    <component :is="as" class="mx-auto w-full px-5 sm:px-6 lg:px-8" :class="width">
        <slot />
    </component>
</template>
