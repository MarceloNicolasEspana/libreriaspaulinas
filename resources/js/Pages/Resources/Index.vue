<script setup>
import { Link } from '@inertiajs/vue3';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Container from '@/Components/Container.vue';
import ResourceCard from '@/Components/ResourceCard.vue';
import Pagination from '@/Components/Pagination.vue';
import EmptyState from '@/Components/EmptyState.vue';
defineProps({
    seo: { type: Object, required: true },
    resources: { type: Object, required: true },
    categories: { type: Array, required: true },
    selectedCategory: { type: String, required: true },
    indexHref: { type: String, required: true },
});
</script>
<template>
    <div>
        <Container class="py-14 sm:py-20">
            <Breadcrumbs :items="seo.breadcrumbs" />

            <header class="mb-10 max-w-3xl">
                <p class="text-sm font-semibold tracking-widest text-accent-700 uppercase">Para enseñar y compartir</p>
                <h1 class="mt-3 font-serif text-4xl text-brand-950 sm:text-5xl">Recursos que acompañan</h1>
                <p class="mt-5 text-lg leading-relaxed text-paper-600">
                    Ideas, guías y materiales para la sala de clases, la comunidad y la parroquia. Encuentra un apoyo
                    para cada encuentro.
                </p>
            </header>
            <nav aria-label="Categorías de recursos" class="mb-8 flex flex-wrap gap-2">
                <Link
                    :href="indexHref"
                    class="rounded-full border px-4 py-2 text-sm"
                    :class="!selectedCategory ? 'bg-brand-900 text-white' : 'border-paper-200 text-brand-900'"
                    :aria-current="!selectedCategory ? 'page' : undefined"
                    >Todos</Link
                >
                <Link
                    v-for="category in categories"
                    :key="category.slug"
                    :href="category.href"
                    class="rounded-full border px-4 py-2 text-sm hover:border-brand-600"
                    :class="
                        selectedCategory === category.slug
                            ? 'bg-brand-900 text-white'
                            : 'border-paper-200 text-brand-900'
                    "
                    :aria-current="selectedCategory === category.slug ? 'page' : undefined"
                    >{{ category.name }}</Link
                >
            </nav>
            <p class="mb-5 text-sm text-paper-600">{{ resources.total }} recursos disponibles</p>
            <ul v-if="resources.data.length" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <li v-for="resource in resources.data" :key="resource.id">
                    <ResourceCard v-bind="resource" tone="paper" />
                </li>
            </ul>
            <EmptyState
                v-else
                title="Pronto compartiremos nuevos materiales"
                description="Puedes explorar las demás categorías para encontrar recursos disponibles."
                ><template #action
                    ><Link :href="indexHref" class="text-brand-800 underline">Ver todos los recursos</Link></template
                ></EmptyState
            >
            <Pagination :links="resources.links" />
        </Container>
    </div>
</template>
