<script setup>
import { computed } from 'vue';
import ChevronDownIcon from '@/Components/Icons/ChevronDownIcon.vue';
import FilterIcon from '@/Components/Icons/FilterIcon.vue';
import { useCatalogFilters } from '@/Composables/useCatalogFilters';

/**
 * Barra sobre la cuadrícula: cuántos títulos hay, cómo se ordenan y, en móvil,
 * el acceso al panel de filtros.
 */
const props = defineProps({
    filters: { type: Object, required: true },
    /** Opciones de orden que ofrece el servidor. */
    sorts: { type: Array, required: true },
    /** Paginador de Laravel, para el recuento. */
    products: { type: Object, required: true },
    /** Filtros puestos, para el distintivo del botón en móvil. */
    activeCount: { type: Number, default: 0 },
});

defineEmits(['open-filters']);

const { sort } = useCatalogFilters(() => props.filters);

/**
 * "24 de 137 títulos", o "137 títulos" cuando caben todos en una página.
 */
const summary = computed(() => {
    const { total, from, to } = props.products;

    if (total === 0) {
        return 'Sin resultados';
    }

    const noun = total === 1 ? 'título' : 'títulos';

    return total <= props.products.per_page ? `${total} ${noun}` : `${from}–${to} de ${total} ${noun}`;
});
</script>

<template>
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-paper-200 pb-4">
        <div class="flex items-center gap-3">
            <!-- En escritorio la barra de filtros ya está a la vista. -->
            <button
                type="button"
                class="inline-flex h-10 items-center gap-2 rounded-control border border-brand-200 bg-white px-4 text-sm font-medium text-brand-800 transition-colors hover:border-brand-300 hover:bg-brand-50 active:bg-brand-100 lg:hidden"
                @click="$emit('open-filters')"
            >
                <FilterIcon class="size-4.5" />
                Filtros
                <span
                    v-if="activeCount"
                    class="inline-flex size-5 items-center justify-center rounded-full bg-accent-600 text-xs font-semibold text-white"
                >
                    {{ activeCount }}
                </span>
            </button>

            <p class="text-sm text-paper-600" aria-live="polite">{{ summary }}</p>
        </div>

        <div class="flex items-center gap-2">
            <label for="catalogo-orden" class="text-sm whitespace-nowrap text-paper-600">Ordenar por</label>

            <div class="relative">
                <select
                    id="catalogo-orden"
                    class="h-10 appearance-none rounded-control border border-paper-200 bg-white pr-9 pl-3 text-sm text-paper-900 transition-colors hover:border-brand-300 focus:border-brand-400 focus:outline-none"
                    :value="filters.orden"
                    @change="sort($event.target.value)"
                >
                    <option v-for="option in sorts" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>

                <ChevronDownIcon
                    class="pointer-events-none absolute top-1/2 right-3 size-4 -translate-y-1/2 text-paper-500"
                />
            </div>
        </div>
    </div>
</template>
