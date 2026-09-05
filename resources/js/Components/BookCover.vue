<script setup>
import { computed } from 'vue';

/**
 * Portada de un título.
 *
 * Mientras el catálogo no tenga fotografías, la portada se dibuja con tipografía
 * y color. En cuanto exista la imagen real basta con entregarla en "image": el
 * resto de la interfaz no cambia.
 */
const props = defineProps({
    title: { type: String, required: true },
    author: { type: String, default: null },
    category: { type: String, default: null },
    image: { type: String, default: null },
});

/*
 * El tono se deriva del título para que un mismo libro se vea siempre igual y
 * la cuadrícula no repita colores por azar entre recargas.
 */
const palettes = [
    'from-brand-700 to-brand-950',
    'from-accent-800 to-accent-950',
    'from-brand-600 to-brand-900',
    'from-paper-700 to-paper-950',
    'from-gold-600 to-brand-900',
];

const palette = computed(() => {
    let hash = 0;

    for (const character of props.title) {
        hash = (hash * 31 + character.codePointAt(0)) % 9973;
    }

    return palettes[hash % palettes.length];
});
</script>

<template>
    <div
        class="relative aspect-[3/4] overflow-hidden rounded-l-sm rounded-r-card bg-brand-900 shadow-card"
        :class="image ? '' : ['bg-linear-to-br', palette]"
    >
        <img v-if="image" :src="image" :alt="`Portada de ${title}`" class="size-full object-cover" />

        <template v-else>
            <!-- Lomo: da volumen al bloque sin recurrir a una fotografía. -->
            <div class="absolute inset-y-0 left-0 w-[7%] bg-brand-950/35" aria-hidden="true" />
            <div class="absolute inset-y-0 left-[7%] w-px bg-white/20" aria-hidden="true" />

            <div class="flex h-full flex-col justify-between py-4 pr-3 pl-[14%] sm:py-5 sm:pr-4">
                <p v-if="category" class="text-[0.6rem] font-semibold tracking-[0.16em] text-white/60 uppercase">
                    {{ category }}
                </p>

                <div>
                    <p class="line-clamp-4 font-serif text-sm leading-snug font-semibold text-white sm:text-base">
                        {{ title }}
                    </p>
                    <div class="mt-2.5 h-px w-8 bg-gold-200/70" aria-hidden="true" />
                    <p v-if="author" class="mt-2 line-clamp-1 text-[0.7rem] text-white/75">{{ author }}</p>
                </div>

                <p class="text-[0.6rem] font-semibold tracking-[0.2em] text-white/60 uppercase">Paulinas</p>
            </div>
        </template>
    </div>
</template>
