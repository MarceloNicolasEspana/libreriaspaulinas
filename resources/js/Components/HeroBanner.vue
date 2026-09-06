<script setup>
import { computed } from 'vue';
import Button from '@/Components/Button.vue';
import Container from '@/Components/Container.vue';

/**
 * Banner de campaña reutilizable.
 *
 * No conoce ninguna campaña en particular: todo su contenido llega por props,
 * de modo que activar, pausar o reemplazar una campaña sea un cambio de datos.
 */
const props = defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: null },
    description: { type: String, default: null },
    image: { type: String, default: null },
    imageAlt: { type: String, default: '' },
    buttonLabel: { type: String, default: null },
    buttonHref: { type: String, default: null },
    align: {
        type: String,
        default: 'start',
        validator: (value) => ['start', 'center'].includes(value),
    },
    /** Una campaña inactiva no se muestra: el componente no renderiza nada. */
    active: { type: Boolean, default: true },
});

/*
 * Dos composiciones, según la alineación pedida:
 *
 *   start  → texto e imagen en columnas contiguas.
 *   center → texto centrado sobre la imagen, que pasa al fondo.
 */
const isSplit = computed(() => props.align === 'start' && Boolean(props.image));
const isBackground = computed(() => props.align === 'center' && Boolean(props.image));

const hasButton = computed(() => Boolean(props.buttonLabel && props.buttonHref));
</script>

<template>
    <section v-if="active" class="relative overflow-hidden bg-brand-950 text-white">
        <!-- Imagen de fondo, solo en la composición centrada. -->
        <img
            v-if="isBackground"
            :src="image"
            :alt="imageAlt"
            fetchpriority="high"
            decoding="async"
            class="absolute inset-0 size-full object-cover"
        />
        <div v-if="isBackground" class="absolute inset-0 bg-brand-950/75" aria-hidden="true" />

        <!-- Halos cálidos: dan luz al bloque incluso sin imagen. -->
        <div
            class="pointer-events-none absolute -top-32 -right-24 size-[32rem] rounded-full bg-brand-700/40 blur-3xl"
            aria-hidden="true"
        />
        <div
            class="pointer-events-none absolute -bottom-40 -left-32 size-[28rem] rounded-full bg-gold-600/15 blur-3xl"
            aria-hidden="true"
        />

        <Container class="relative">
            <div
                class="grid items-center gap-8 py-14 sm:py-16 lg:gap-14 lg:py-20"
                :class="isSplit ? 'lg:grid-cols-2' : ''"
            >
                <div :class="align === 'center' ? 'mx-auto max-w-2xl text-center' : 'max-w-xl'">
                    <p
                        v-if="subtitle"
                        class="mb-4 text-xs font-semibold tracking-[0.18em] text-gold-200 uppercase sm:text-sm"
                    >
                        {{ subtitle }}
                    </p>

                    <h1
                        class="font-serif text-3xl leading-tight font-semibold text-balance text-white sm:text-4xl lg:text-5xl"
                    >
                        {{ title }}
                    </h1>

                    <p v-if="description" class="mt-5 text-base leading-relaxed text-brand-100 sm:text-lg">
                        {{ description }}
                    </p>

                    <div
                        v-if="hasButton || $slots.actions"
                        class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap"
                        :class="align === 'center' ? 'sm:justify-center' : ''"
                    >
                        <Button v-if="hasButton" :href="buttonHref" size="lg">{{ buttonLabel }}</Button>
                        <slot name="actions" />
                    </div>
                </div>

                <!-- La imagen va después del texto: en móvil el titular abre la página. -->
                <div v-if="isSplit" class="relative">
                    <img
                        :src="image"
                        :alt="imageAlt"
                        fetchpriority="high"
                        decoding="async"
                        class="aspect-[4/3] w-full rounded-card object-cover shadow-raised ring-1 ring-white/10"
                    />
                </div>
            </div>
        </Container>
    </section>
</template>
