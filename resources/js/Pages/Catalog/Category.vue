<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';
import Pagination from '@/Components/Pagination.vue';
import ProductGrid from '@/Components/ProductGrid.vue';
import SectionHeader from '@/Components/SectionHeader.vue';

defineProps({
    seo: { type: Object, required: true },
    category: { type: Object, required: true },
    products: { type: Object, required: true },
});
</script>

<template>
    <Head :title="seo.title">
        <meta name="description" :content="seo.description" />
    </Head>

    <Container class="py-14 sm:py-16 lg:py-20">
        <SectionHeader
            as="h1"
            :eyebrow="category.parent ? category.parent.name : 'Sección'"
            :title="category.name"
            :subtitle="category.description"
        />

        <!-- Subsecciones: el listado ya incluye sus títulos, esto solo acota. -->
        <ul v-if="category.children.length" class="mb-10 flex flex-wrap gap-2">
            <li v-for="child in category.children" :key="child.href">
                <Link
                    :href="child.href"
                    class="inline-flex rounded-full border border-paper-200 bg-white px-4 py-1.5 text-sm text-brand-800 transition-colors hover:border-brand-200 hover:bg-brand-50"
                >
                    {{ child.name }}
                </Link>
            </li>
        </ul>

        <ProductGrid :products="products.data" empty-message="Todavía no hay títulos publicados en esta sección." />

        <Pagination :links="products.links" />
    </Container>
</template>
