<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

/**
 * Paginador de los listados del catálogo.
 *
 * Recibe los "links" que arma Laravel. Se omite entero cuando hay una sola
 * página: un paginador de un solo número es ruido.
 */
const props = defineProps({
    links: { type: Array, required: true },
    /**
     * Conserva la página viva al cambiar de número. Lo usa el catálogo, donde
     * el panel de filtros no debe cerrarse ni reiniciarse por pasar de página.
     */
    preserveState: { type: Boolean, default: false },
});

/*
 * Laravel rotula las flechas con entidades HTML. Se traducen a texto en lugar
 * de inyectarlas con v-html: es una etiqueta, no marcado.
 */
const entities = { '&laquo;': '«', '&raquo;': '»' };

const pages = computed(() =>
    props.links.map((link) => ({
        ...link,
        label: Object.entries(entities).reduce(
            (label, [entity, character]) => label.replaceAll(entity, character),
            link.label,
        ),
    })),
);

const isVisible = computed(() => props.links.filter((link) => link.url !== null).length > 1);
</script>

<template>
    <nav v-if="isVisible" class="mt-12 flex justify-center" aria-label="Paginación">
        <ul class="flex flex-wrap items-center gap-1">
            <!-- La clave es el índice: Laravel rotula con "..." los saltos, y
                 puede haber dos en la misma lista. -->
            <li v-for="(page, index) in pages" :key="index">
                <Link
                    v-if="page.url"
                    :href="page.url"
                    :preserve-state="preserveState"
                    class="inline-flex min-w-9 items-center justify-center rounded-md border px-3 py-2 text-sm transition-colors"
                    :class="
                        page.active
                            ? 'border-brand-700 bg-brand-700 text-white'
                            : 'border-paper-200 bg-white text-brand-800 hover:border-brand-200 hover:bg-brand-50'
                    "
                    :aria-current="page.active ? 'page' : undefined"
                >
                    {{ page.label }}
                </Link>

                <span v-else class="inline-flex min-w-9 items-center justify-center px-3 py-2 text-sm text-paper-600">
                    {{ page.label }}
                </span>
            </li>
        </ul>
    </nav>
</template>
