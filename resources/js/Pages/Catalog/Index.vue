<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import CatalogFilters from '@/Components/CatalogFilters.vue';
import CatalogToolbar from '@/Components/CatalogToolbar.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Container from '@/Components/Container.vue';
import EmptyState from '@/Components/EmptyState.vue';
import FilterDrawer from '@/Components/FilterDrawer.vue';
import Pagination from '@/Components/Pagination.vue';
import ProductGrid from '@/Components/ProductGrid.vue';
import SectionHeader from '@/Components/SectionHeader.vue';
import CloseIcon from '@/Components/Icons/CloseIcon.vue';
import { useCatalogFilters } from '@/Composables/useCatalogFilters';

/**
 * Catálogo.
 *
 * La página no guarda el estado de la búsqueda: lo lee de las props, que salen
 * de la query string y las resuelve Laravel. Cambiar un filtro es navegar, y
 * navegar es lo que actualiza la cuadrícula.
 */
const props = defineProps({
    seo: { type: Object, required: true },
    heading: { type: String, required: true },
    subtitle: { type: String, default: null },
    /** Paginador de Laravel: { data, links, total, ... }. */
    products: { type: Object, required: true },
    /** Filtros vigentes, con las mismas claves que la URL. */
    filters: { type: Object, required: true },
    /** Opciones que ofrece cada faceta, ya contadas por el servidor. */
    options: { type: Object, required: true },
    /** Filtros puestos, con etiqueta legible, para quitarlos de a uno. */
    chips: { type: Array, required: true },
    /** Secciones propuestas cuando no hay resultados. */
    suggestions: { type: Array, default: () => [] },
});

const { remove, clear, activeCount } = useCatalogFilters(() => props.filters);

// Estado de la interfaz, no del catálogo: por eso no viaja a la URL.
const drawerOpen = ref(false);

const active = computed(() => activeCount());
</script>

<template>
    <Container size="wide" class="py-14 sm:py-16 lg:py-20">
        <Breadcrumbs :items="seo.breadcrumbs" />

        <SectionHeader as="h1" eyebrow="Catálogo" :title="heading" :subtitle="subtitle" />

        <div class="lg:grid lg:grid-cols-[16rem_1fr] lg:gap-10">
            <!-- En escritorio la barra acompaña al listado; en móvil se abre en
                 un cajón desde el botón "Filtros" de la barra de herramientas. -->
            <aside class="hidden lg:block">
                <!-- La lista de filtros puede ser más alta que la ventana:
                     se queda pegada y hace scroll por dentro. -->
                <div class="sticky top-28 max-h-[calc(100vh-9rem)] overflow-y-auto overscroll-contain pr-1">
                    <CatalogFilters :filters="filters" :options="options" id-prefix="filtros-escritorio" />
                </div>
            </aside>

            <div class="min-w-0">
                <CatalogToolbar
                    :filters="filters"
                    :sorts="options.orden"
                    :products="products"
                    :active-count="active"
                    @open-filters="drawerOpen = true"
                />

                <!-- Filtros puestos: se ven de un vistazo y se quitan de a uno. -->
                <ul v-if="chips.length" class="mt-4 flex flex-wrap items-center gap-2">
                    <li v-for="chip in chips" :key="chip.key">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-full border border-brand-200 bg-brand-50 py-1.5 pr-2 pl-3 text-sm text-brand-800 transition-colors hover:border-brand-300 hover:bg-brand-100"
                            @click="remove(chip.key)"
                        >
                            <span class="text-paper-600">{{ chip.label }}:</span>
                            {{ chip.value }}
                            <CloseIcon class="size-3.5" />
                            <span class="sr-only">Quitar filtro</span>
                        </button>
                    </li>

                    <li v-if="chips.length > 1">
                        <button
                            type="button"
                            class="px-2 py-1.5 text-sm text-accent-700 underline decoration-accent-300 underline-offset-4 transition-colors hover:decoration-accent-600"
                            @click="clear()"
                        >
                            Limpiar filtros
                        </button>
                    </li>
                </ul>

                <div class="mt-8">
                    <ProductGrid :products="products.data">
                        <template #empty>
                            <EmptyState
                                title="No encontramos libros para tu búsqueda."
                                description="Prueba con otras palabras o quita alguno de los filtros."
                            >
                                <template #action>
                                    <button
                                        type="button"
                                        class="inline-flex h-11 items-center justify-center rounded-control bg-accent-600 px-5 text-sm font-medium text-white transition-colors hover:bg-accent-700 active:bg-accent-800"
                                        @click="clear()"
                                    >
                                        Limpiar filtros
                                    </button>
                                </template>

                                <div v-if="suggestions.length" class="mt-8">
                                    <p class="text-sm font-medium text-paper-700">O explora una sección:</p>

                                    <ul class="mt-3 flex flex-wrap justify-center gap-2">
                                        <li v-for="suggestion in suggestions" :key="suggestion.href">
                                            <Link
                                                :href="suggestion.href"
                                                class="inline-flex rounded-full border border-paper-200 bg-white px-4 py-1.5 text-sm text-brand-800 transition-colors hover:border-brand-200 hover:bg-brand-50"
                                            >
                                                {{ suggestion.name }}
                                            </Link>
                                        </li>
                                    </ul>
                                </div>
                            </EmptyState>
                        </template>
                    </ProductGrid>

                    <!-- preserve-state: pasar de página no debe cerrar el cajón
                         de filtros ni reiniciar la barra lateral. -->
                    <Pagination :links="products.links" preserve-state />
                </div>
            </div>
        </div>
    </Container>

    <FilterDrawer :open="drawerOpen" @close="drawerOpen = false">
        <CatalogFilters :filters="filters" :options="options" id-prefix="filtros-movil" />

        <template #footer>
            <button
                type="button"
                class="inline-flex h-11 w-full items-center justify-center rounded-control bg-accent-600 text-sm font-medium text-white transition-colors hover:bg-accent-700 active:bg-accent-800"
                @click="drawerOpen = false"
            >
                Ver {{ products.total }} {{ products.total === 1 ? 'título' : 'títulos' }}
            </button>
        </template>
    </FilterDrawer>
</template>
