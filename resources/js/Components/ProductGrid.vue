<script setup>
import ProductCard from '@/Components/ProductCard.vue';

/**
 * Cuadrícula de títulos. Es la misma en la portada, en /libros, en la sección y
 * en la página de autor: cambia el origen de los datos, no la presentación.
 */
defineProps({
    products: { type: Array, required: true },
    /** Texto para cuando la consulta no devuelve nada. */
    emptyMessage: { type: String, default: 'Todavía no hay títulos en esta sección.' },
});
</script>

<template>
    <ul v-if="products.length" class="grid grid-cols-2 gap-x-5 gap-y-9 sm:grid-cols-3 sm:gap-x-6 lg:grid-cols-4">
        <li v-for="product in products" :key="product.id">
            <ProductCard v-bind="product" />
        </li>
    </ul>

    <!-- El estado vacío por defecto es un aviso simple; el catálogo entrega el
         suyo por la ranura, con opciones para seguir. -->
    <slot v-else name="empty">
        <p class="rounded-card border border-paper-200 bg-paper-50 px-6 py-10 text-center text-paper-600">
            {{ emptyMessage }}
        </p>
    </slot>
</template>
