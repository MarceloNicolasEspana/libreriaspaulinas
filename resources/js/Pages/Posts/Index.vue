<script setup>
import { Head } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';
import PostCard from '@/Components/PostCard.vue';
import Pagination from '@/Components/Pagination.vue';
import EmptyState from '@/Components/EmptyState.vue';
defineProps({ posts: { type: Object, required: true } });
</script>
<template>
    <div>
        <Head title="Novedades"
            ><meta
                name="description"
                content="Lecturas, encuentros y vida de comunidad. Descubre las novedades de Paulinas."
        /></Head>
        <Container class="py-14 sm:py-20">
            <header class="mb-12 border-b border-brand-900 pb-8">
                <p class="text-sm font-semibold tracking-widest text-accent-700 uppercase">
                    Lecturas · Encuentros · Comunidad
                </p>
                <h1 class="mt-4 font-serif text-5xl text-brand-950 sm:text-7xl">Novedades</h1>
                <p class="mt-5 max-w-2xl text-lg text-paper-600">
                    Historias e ideas para seguir creciendo en la fe, la cultura y el encuentro con los demás.
                </p>
            </header>
            <template v-if="posts.data.length"
                ><PostCard :post="posts.data[0]" featured />
                <div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <PostCard v-for="post in posts.data.slice(1)" :key="post.id" :post="post" /></div
            ></template>
            <EmptyState
                v-else
                title="Nuevas historias, muy pronto"
                description="Aquí encontrarás las próximas novedades de nuestra comunidad."
            />
            <Pagination :links="posts.links" />
        </Container>
    </div>
</template>
