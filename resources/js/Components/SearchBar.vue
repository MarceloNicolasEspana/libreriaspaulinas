<script setup>
import { ref, useId, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import SearchIcon from '@/Components/Icons/SearchIcon.vue';

const props = defineProps({
    /** "hero" para el buscador destacado del header, "compact" en el menú móvil. */
    size: {
        type: String,
        default: 'hero',
        validator: (v) => ['hero', 'compact'].includes(v),
    },
    autofocus: { type: Boolean, default: false },
    /** Término con el que llega la página, para que el campo no aparezca vacío
     *  después de buscar. */
    initial: { type: String, default: '' },
    /**
     * Si el propio componente navega al enviar. El catálogo lo desactiva: allí
     * la búsqueda tiene que conservar los filtros que ya estaban puestos, y de
     * eso se encarga la página.
     */
    navigate: { type: Boolean, default: true },
});

const emit = defineEmits(['submitted']);

/*
 * El buscador se monta varias veces en la misma página —header, menú móvil y
 * barra de filtros— y dos campos con el mismo id romperían la relación entre
 * etiqueta y control para el lector de pantalla.
 */
const inputId = `search-${useId()}`;

const query = ref(props.initial);

// El header vive fuera de la página, así que sigue montado entre navegaciones:
// sin esto, el campo conservaría el término de la búsqueda anterior.
watch(
    () => props.initial,
    (term) => {
        query.value = term;
    },
);

/*
 * La búsqueda se resuelve en /libros: es la página que sabe interpretar "q"
 * junto con el resto de los filtros. No hay una página de resultados aparte
 * porque buscar es una forma más de acotar el catálogo.
 */
function submit() {
    const term = query.value.trim();

    emit('submitted', term);

    if (props.navigate) {
        router.get('/libros', term === '' ? {} : { q: term }, { preserveState: false });
    }
}
</script>

<template>
    <form role="search" class="relative w-full" @submit.prevent="submit">
        <label :for="inputId" class="sr-only">Buscar en el catálogo</label>

        <SearchIcon
            class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-paper-500"
            :class="props.size === 'hero' ? 'size-5' : 'size-4.5'"
        />

        <input
            :id="inputId"
            v-model="query"
            type="search"
            name="q"
            :autofocus="autofocus"
            placeholder="Buscar libros, autores, temas..."
            class="w-full rounded-full border border-brand-200 bg-brand-50/60 pr-28 pl-11 text-paper-900 transition-colors duration-150 placeholder:text-paper-500 hover:border-brand-300 focus:border-brand-400 focus:bg-white focus:outline-none"
            :class="props.size === 'hero' ? 'h-12 text-base' : 'h-11 text-sm'"
        />

        <!--
            En compacto el botón queda como icono: en 375 px el texto "Buscar"
            recortaba el placeholder.
        -->
        <button
            type="submit"
            class="absolute top-1/2 right-1.5 inline-flex -translate-y-1/2 items-center justify-center rounded-full bg-accent-600 font-medium text-white transition-colors duration-150 hover:bg-accent-700 active:bg-accent-800"
            :class="props.size === 'hero' ? 'h-9 px-5 text-sm' : 'size-9'"
        >
            <span v-if="props.size === 'hero'">Buscar</span>
            <template v-else>
                <SearchIcon class="size-4.5" />
                <span class="sr-only">Buscar</span>
            </template>
        </button>
    </form>
</template>
