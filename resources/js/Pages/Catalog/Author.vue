<script setup>
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Container from '@/Components/Container.vue';
import Pagination from '@/Components/Pagination.vue';
import ProductGrid from '@/Components/ProductGrid.vue';
import SectionHeader from '@/Components/SectionHeader.vue';

defineProps({
    seo: { type: Object, required: true },
    author: { type: Object, required: true },
    products: { type: Object, required: true },
});
</script>

<template>
    <Container class="py-14 sm:py-16 lg:py-20">
        <Breadcrumbs :items="seo.breadcrumbs" />

        <SectionHeader as="h1" eyebrow="Autor" :title="author.name" :subtitle="author.biography" />

        <ProductGrid :products="products.data" empty-message="Todavía no hay títulos publicados de este autor." />

        <Pagination :links="products.links" />
    </Container>
</template>
