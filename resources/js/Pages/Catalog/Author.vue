<script setup>
import { Head } from '@inertiajs/vue3';
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
    <Head :title="seo.title">
        <meta name="description" :content="seo.description" />
    </Head>

    <Container class="py-14 sm:py-16 lg:py-20">
        <SectionHeader as="h1" eyebrow="Autor" :title="author.name" :subtitle="author.biography" />

        <ProductGrid :products="products.data" empty-message="Todavía no hay títulos publicados de este autor." />

        <Pagination :links="products.links" />
    </Container>
</template>
