<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({ post: { type: Object, required: true }, featured: { type: Boolean, default: false } });
</script>
<template>
    <article
        class="group h-full border-b border-paper-200 pb-8"
        :class="featured ? 'grid gap-8 md:grid-cols-2 md:items-center' : ''"
    >
        <Link :href="post.href" class="block overflow-hidden bg-brand-100" :aria-label="post.title">
            <img
                v-if="post.image"
                :src="post.image"
                :alt="post.title"
                loading="lazy"
                class="aspect-[16/10] w-full object-cover transition-transform duration-300 group-hover:scale-105"
            />
            <div v-else class="flex aspect-[16/10] items-end bg-brand-950 p-8 text-gold-200">
                <span class="font-serif text-4xl italic">Palabras que nos unen.</span>
            </div>
        </Link>
        <div class="flex flex-col gap-4" :class="featured ? '' : 'pt-6'">
            <time
                :datetime="post.published_at"
                class="text-xs font-semibold tracking-widest text-accent-700 uppercase"
                >{{ post.date }}</time
            >
            <h2 class="font-serif leading-tight text-brand-950" :class="featured ? 'text-3xl sm:text-4xl' : 'text-2xl'">
                <Link :href="post.href" class="hover:text-accent-700">{{ post.title }}</Link>
            </h2>
            <p class="leading-relaxed text-paper-600">{{ post.excerpt }}</p>
            <Link :href="post.href" class="text-sm font-semibold text-brand-800 underline underline-offset-4"
                >Leer la historia<span class="sr-only">: {{ post.title }}</span></Link
            >
        </div>
    </article>
</template>
