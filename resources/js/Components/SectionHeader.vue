<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: null },
    eyebrow: { type: String, default: null },
    as: { type: String, default: 'h2' },
    align: {
        type: String,
        default: 'start',
        validator: (v) => ['start', 'center'].includes(v),
    },
    /** "inverse" para las secciones sobre fondo oscuro. */
    tone: {
        type: String,
        default: 'default',
        validator: (v) => ['default', 'inverse'].includes(v),
    },
});

const isInverse = computed(() => props.tone === 'inverse');
</script>

<template>
    <header class="mb-8 sm:mb-10" :class="align === 'center' ? 'text-center' : ''">
        <!-- La acción se acuesta bajo el título en móvil y vuelve al costado desde sm. -->
        <div class="sm:flex sm:items-end sm:justify-between sm:gap-8">
            <div :class="align === 'center' ? 'mx-auto' : ''">
                <p
                    v-if="eyebrow"
                    class="mb-3 text-xs font-semibold tracking-[0.16em] uppercase"
                    :class="isInverse ? 'text-gold-200' : 'text-accent-700'"
                >
                    {{ eyebrow }}
                </p>

                <component
                    :is="as"
                    class="font-serif text-2xl font-semibold text-balance sm:text-3xl lg:text-4xl"
                    :class="isInverse ? 'text-white' : ''"
                >
                    {{ title }}
                </component>

                <p
                    v-if="subtitle"
                    class="mt-3 max-w-prose text-base leading-relaxed"
                    :class="[isInverse ? 'text-brand-100' : 'text-paper-600', align === 'center' ? 'mx-auto' : '']"
                >
                    {{ subtitle }}
                </p>
            </div>

            <div v-if="$slots.action" class="mt-5 shrink-0 sm:mt-0">
                <slot name="action" />
            </div>
        </div>

        <slot />
    </header>
</template>
