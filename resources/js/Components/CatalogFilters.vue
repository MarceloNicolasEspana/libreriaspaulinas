<script setup>
import { computed, ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import SearchBar from '@/Components/SearchBar.vue';
import ChevronDownIcon from '@/Components/Icons/ChevronDownIcon.vue';
import { useCatalogFilters } from '@/Composables/useCatalogFilters';
import { useCurrency } from '@/Composables/useCurrency';

/**
 * Barra de filtros del catálogo.
 *
 * Cada opción es un enlace real a la URL filtrada, no un botón: así el catálogo
 * acotado se puede abrir en otra pestaña, compartir e indexar. La navegación la
 * hace Inertia conservando el estado, de modo que el panel no se cierra ni
 * pierde su scroll mientras la cuadrícula se actualiza.
 *
 * El componente se monta dos veces —a un costado en escritorio y dentro del
 * cajón en móvil—, por eso "idPrefix": dos campos con el mismo id romperían la
 * relación entre etiqueta y control.
 */
const props = defineProps({
    filters: { type: Object, required: true },
    options: { type: Object, required: true },
    idPrefix: { type: String, default: 'filtros' },
});

const { href, apply, clear, search } = useCatalogFilters(() => props.filters);
const { format } = useCurrency();

/**
 * Cuántas opciones se muestran antes de ofrecer "Ver todas". Con catálogos
 * grandes la lista de autores es larga y ocuparía la barra entera.
 */
const VISIBLE = 8;

/**
 * Facetas de un solo valor. La sección va aparte porque es un árbol.
 */
const facets = computed(() =>
    [
        { key: 'autor', label: 'Autor', options: props.options.autores, searchable: true },
        { key: 'editorial', label: 'Editorial', options: props.options.editoriales },
        { key: 'coleccion', label: 'Colección', options: props.options.colecciones },
        { key: 'disponibilidad', label: 'Disponibilidad', options: props.options.disponibilidad },
    ].filter((facet) => facet.options.length),
);

/*
 * Estado local del panel, no del catálogo: qué listas están desplegadas y qué
 * se escribió en el buscador de opciones. No viaja a la URL porque no cambia
 * los resultados, solo lo que se ve de la barra.
 */
const expanded = ref({});
const optionQuery = ref({});

/**
 * Opciones que quedan tras el buscador de la propia lista.
 *
 * Es una comodidad para encontrar un nombre dentro de una lista larga que ya
 * está en la página; los resultados del catálogo los sigue calculando Laravel.
 */
function matching(facet) {
    const term = (optionQuery.value[facet.key] ?? '').trim().toLowerCase();

    return term === '' ? facet.options : facet.options.filter((o) => o.label.toLowerCase().includes(term));
}

function shown(facet) {
    const options = matching(facet);

    return expanded.value[facet.key] ? options : options.slice(0, VISIBLE);
}

function hiddenCount(facet) {
    return Math.max(0, matching(facet).length - VISIBLE);
}

function isSelected(key, value) {
    return props.filters[key] === value;
}

/** Un enlace a la opción, o a quitarla si ya estaba puesta. */
function optionHref(key, value) {
    return href({ [key]: isSelected(key, value) ? null : value });
}

/*
 * Los dos campos de precio son un formulario: se aplican al enviar y no en cada
 * tecla, para no disparar una navegación por dígito escrito.
 *
 * Se sincronizan con la URL porque preserveState mantiene vivo el componente:
 * al quitar el filtro desde otra parte de la página, los campos deben vaciarse.
 */
const price = ref({ min: '', max: '' });

watch(
    () => [props.filters.precio_min, props.filters.precio_max],
    ([min, max]) => {
        price.value = { min: min ?? '', max: max ?? '' };
    },
    { immediate: true },
);

function applyPrice() {
    apply({
        precio_min: price.value.min === '' ? null : Number(price.value.min),
        precio_max: price.value.max === '' ? null : Number(price.value.max),
    });
}

const hasPrice = computed(() => props.options.precio.max > 0);

const isFiltered = computed(() =>
    Object.entries(props.filters).some(([key, value]) => key !== 'orden' && value !== null && value !== ''),
);
</script>

<template>
    <div class="text-sm">
        <div class="flex items-center justify-between gap-3 pb-4">
            <h2 class="font-serif text-lg font-semibold text-brand-900">Filtros</h2>

            <button
                v-if="isFiltered"
                type="button"
                class="text-sm text-accent-700 underline decoration-accent-300 underline-offset-4 transition-colors hover:decoration-accent-600"
                @click="clear()"
            >
                Limpiar filtros
            </button>
        </div>

        <!--
            Buscar es una forma más de acotar el catálogo, así que el campo vive
            con el resto de los filtros. navigate="false": a diferencia del
            buscador del header, que empieza de cero, este conserva lo que ya
            estaba puesto.
        -->
        <div class="border-t border-paper-200 py-4">
            <SearchBar size="compact" :initial="filters.q ?? ''" :navigate="false" @submitted="search" />
        </div>

        <!-- Secciones: árbol de dos niveles, el mismo que ordena el catálogo. -->
        <details v-if="options.categorias.length" open class="group border-t border-paper-200 py-4">
            <summary
                class="flex cursor-pointer list-none items-center justify-between font-medium text-brand-900 [&::-webkit-details-marker]:hidden"
            >
                Sección
                <ChevronDownIcon class="size-4 text-paper-500 transition-transform group-open:rotate-180" />
            </summary>

            <ul class="mt-3 space-y-1">
                <li v-for="category in options.categorias" :key="category.value">
                    <Link
                        :href="optionHref('categoria', category.value)"
                        preserve-state
                        preserve-scroll
                        class="flex items-baseline justify-between gap-2 rounded-md px-2 py-1.5 transition-colors"
                        :class="
                            isSelected('categoria', category.value)
                                ? 'bg-brand-50 font-medium text-brand-900'
                                : 'text-paper-700 hover:bg-paper-50 hover:text-brand-900'
                        "
                        :aria-current="isSelected('categoria', category.value) ? 'true' : undefined"
                    >
                        <span>{{ category.label }}</span>
                        <span class="shrink-0 text-xs text-paper-600">{{ category.count }}</span>
                    </Link>

                    <ul v-if="category.children.length" class="mt-1 ml-3 space-y-1 border-l border-paper-200 pl-2">
                        <li v-for="child in category.children" :key="child.value">
                            <Link
                                :href="optionHref('categoria', child.value)"
                                preserve-state
                                preserve-scroll
                                class="flex items-baseline justify-between gap-2 rounded-md px-2 py-1.5 text-[0.8125rem] transition-colors"
                                :class="
                                    isSelected('categoria', child.value)
                                        ? 'bg-brand-50 font-medium text-brand-900'
                                        : 'text-paper-600 hover:bg-paper-50 hover:text-brand-900'
                                "
                                :aria-current="isSelected('categoria', child.value) ? 'true' : undefined"
                            >
                                <span>{{ child.label }}</span>
                                <span class="shrink-0 text-xs text-paper-600">{{ child.count }}</span>
                            </Link>
                        </li>
                    </ul>
                </li>
            </ul>
        </details>

        <!-- Autor, editorial, colección y disponibilidad. -->
        <details v-for="facet in facets" :key="facet.key" open class="group border-t border-paper-200 py-4">
            <summary
                class="flex cursor-pointer list-none items-center justify-between font-medium text-brand-900 [&::-webkit-details-marker]:hidden"
            >
                {{ facet.label }}
                <ChevronDownIcon class="size-4 text-paper-500 transition-transform group-open:rotate-180" />
            </summary>

            <div v-if="facet.searchable && facet.options.length > VISIBLE" class="mt-3">
                <label :for="`${idPrefix}-${facet.key}-buscar`" class="sr-only">
                    Buscar en la lista de {{ facet.label.toLowerCase() }}
                </label>
                <input
                    :id="`${idPrefix}-${facet.key}-buscar`"
                    v-model="optionQuery[facet.key]"
                    type="search"
                    :placeholder="`Buscar ${facet.label.toLowerCase()}...`"
                    class="h-9 w-full rounded-control border border-paper-200 px-3 text-sm text-paper-900 transition-colors placeholder:text-paper-600 focus:border-brand-400 focus:outline-none"
                />
            </div>

            <ul class="mt-3 space-y-1">
                <li v-for="option in shown(facet)" :key="option.value">
                    <Link
                        :href="optionHref(facet.key, option.value)"
                        preserve-state
                        preserve-scroll
                        class="flex items-baseline justify-between gap-2 rounded-md px-2 py-1.5 transition-colors"
                        :class="
                            isSelected(facet.key, option.value)
                                ? 'bg-brand-50 font-medium text-brand-900'
                                : 'text-paper-700 hover:bg-paper-50 hover:text-brand-900'
                        "
                        :aria-current="isSelected(facet.key, option.value) ? 'true' : undefined"
                    >
                        <span>{{ option.label }}</span>
                        <span class="shrink-0 text-xs text-paper-600">{{ option.count }}</span>
                    </Link>
                </li>

                <li v-if="!shown(facet).length" class="px-2 py-1.5 text-paper-600">Sin coincidencias.</li>
            </ul>

            <button
                v-if="hiddenCount(facet) && !expanded[facet.key]"
                type="button"
                class="mt-2 px-2 text-sm text-accent-700 underline decoration-accent-300 underline-offset-4 transition-colors hover:decoration-accent-600"
                @click="expanded[facet.key] = true"
            >
                Ver {{ hiddenCount(facet) }} más
            </button>

            <button
                v-else-if="expanded[facet.key]"
                type="button"
                class="mt-2 px-2 text-sm text-accent-700 underline decoration-accent-300 underline-offset-4 transition-colors hover:decoration-accent-600"
                @click="expanded[facet.key] = false"
            >
                Ver menos
            </button>
        </details>

        <!-- Precio: los extremos son los del catálogo ya filtrado. -->
        <details v-if="hasPrice" open class="group border-t border-paper-200 py-4">
            <summary
                class="flex cursor-pointer list-none items-center justify-between font-medium text-brand-900 [&::-webkit-details-marker]:hidden"
            >
                Precio
                <ChevronDownIcon class="size-4 text-paper-500 transition-transform group-open:rotate-180" />
            </summary>

            <form class="mt-3" @submit.prevent="applyPrice">
                <p class="mb-3 text-xs text-paper-600">
                    Entre {{ format(options.precio.min) }} y {{ format(options.precio.max) }}
                </p>

                <div class="flex items-center gap-2">
                    <div class="flex-1">
                        <label :for="`${idPrefix}-precio-min`" class="sr-only">Precio mínimo</label>
                        <input
                            :id="`${idPrefix}-precio-min`"
                            v-model="price.min"
                            type="number"
                            inputmode="numeric"
                            min="0"
                            :max="options.precio.max"
                            placeholder="Desde"
                            class="h-10 w-full rounded-control border border-paper-200 px-3 text-sm text-paper-900 transition-colors placeholder:text-paper-600 focus:border-brand-400 focus:outline-none"
                        />
                    </div>

                    <span class="text-paper-500" aria-hidden="true">–</span>

                    <div class="flex-1">
                        <label :for="`${idPrefix}-precio-max`" class="sr-only">Precio máximo</label>
                        <input
                            :id="`${idPrefix}-precio-max`"
                            v-model="price.max"
                            type="number"
                            inputmode="numeric"
                            min="0"
                            :max="options.precio.max"
                            placeholder="Hasta"
                            class="h-10 w-full rounded-control border border-paper-200 px-3 text-sm text-paper-900 transition-colors placeholder:text-paper-600 focus:border-brand-400 focus:outline-none"
                        />
                    </div>
                </div>

                <button
                    type="submit"
                    class="mt-3 h-9 w-full rounded-control border border-brand-200 bg-white text-sm font-medium text-brand-800 transition-colors hover:border-brand-300 hover:bg-brand-50 active:bg-brand-100"
                >
                    Aplicar precio
                </button>
            </form>
        </details>

        <!--
            Facetas anunciadas que todavía no existen en el catálogo. Se muestran
            deshabilitadas en vez de omitirlas: el visitante ve hacia dónde va la
            navegación y nosotros no inventamos datos para rellenarlas.
        -->
        <div v-if="options.pendientes.length" class="border-t border-paper-200 py-4">
            <p class="font-medium text-paper-600">Próximamente</p>

            <ul class="mt-2 space-y-1">
                <li v-for="pending in options.pendientes" :key="pending.value" class="px-2 py-1 text-paper-600">
                    {{ pending.label }}
                </li>
            </ul>
        </div>
    </div>
</template>
