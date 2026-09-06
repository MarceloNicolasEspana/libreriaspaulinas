<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';
defineProps({ post: { type: Object, required: true }, indexHref: { type: String, required: true } });
</script>
<template>
    <div>
        <Head :title="post.title"><meta name="description" :content="post.excerpt" /></Head>
        <Container class="py-14 sm:py-20">
            <article class="mx-auto max-w-3xl">
                <Link :href="indexHref" class="text-sm text-brand-800 underline underline-offset-4"
                    >Volver a novedades</Link
                >
                <header class="mt-10 flex flex-col gap-6">
                    <time
                        :datetime="post.published_at"
                        class="text-sm font-semibold tracking-widest text-accent-700 uppercase"
                        >{{ post.date }}</time
                    >
                    <h1 class="font-serif text-4xl leading-tight text-brand-950 sm:text-6xl">{{ post.title }}</h1>
                    <p class="text-xl leading-relaxed text-paper-600">{{ post.excerpt }}</p>
                </header>
                <img
                    v-if="post.image"
                    :src="post.image"
                    :alt="post.title"
                    class="mt-10 aspect-[16/10] w-full object-cover"
                />
                <div class="mt-10 border-t border-paper-200 pt-10">
                    <p class="text-lg leading-loose whitespace-pre-line text-paper-800">{{ post.content }}</p>
                </div>
                <footer class="mt-12 border-t border-paper-200 pt-6">
                    <Link :href="indexHref" class="font-semibold text-brand-800 underline underline-offset-4"
                        >Más historias de Paulinas</Link
                    >
                </footer>
            </article>
        </Container>
    </div>
</template>
