<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';
import BookIcon from '@/Components/Icons/BookIcon.vue';
import DownloadIcon from '@/Components/Icons/DownloadIcon.vue';
defineProps({ resource: { type: Object, required: true }, indexHref: { type: String, required: true } });
</script>
<template>
    <div>
        <Head :title="resource.title"><meta name="description" :content="resource.description" /></Head>
        <Container class="py-14 sm:py-20">
            <Link :href="indexHref" class="text-sm text-brand-800 underline underline-offset-4">Volver a recursos</Link>
            <article class="mt-8 grid gap-10 lg:grid-cols-[1fr_22rem]">
                <div>
                    <Link
                        :href="resource.categoryHref"
                        class="text-sm font-semibold tracking-widest text-accent-700 uppercase"
                        >{{ resource.category }}</Link
                    >
                    <h1 class="mt-4 font-serif text-4xl leading-tight text-brand-950 sm:text-5xl">
                        {{ resource.title }}
                    </h1>
                    <dl class="my-7 flex flex-wrap gap-6 border-y border-paper-200 py-5 text-sm">
                        <div>
                            <dt class="text-paper-500">Dirigido a</dt>
                            <dd class="mt-1 text-brand-900">{{ resource.audience || 'Toda la comunidad' }}</dd>
                        </div>
                        <div>
                            <dt class="text-paper-500">Publicado</dt>
                            <dd class="mt-1 text-brand-900">
                                <time :datetime="resource.published_at">{{ resource.date }}</time>
                            </dd>
                        </div>
                    </dl>
                    <p class="text-lg leading-relaxed whitespace-pre-line text-paper-700">{{ resource.description }}</p>
                </div>
                <aside class="self-start overflow-hidden rounded-card border border-paper-200 bg-paper-50">
                    <img
                        v-if="resource.thumbnail"
                        :src="resource.thumbnail"
                        :alt="resource.title"
                        class="aspect-[4/3] w-full object-cover"
                    />
                    <div v-else class="grid aspect-[4/3] place-items-center bg-brand-100 text-brand-700">
                        <BookIcon class="size-20" aria-hidden="true" />
                    </div>
                    <div class="flex flex-col gap-4 p-6">
                        <h2 class="font-serif text-2xl text-brand-950">Material para compartir</h2>
                        <template v-if="resource.downloadHref"
                            ><p class="text-sm break-all text-paper-600">{{ resource.file }}</p>
                            <a
                                :href="resource.downloadHref"
                                class="inline-flex items-center justify-center gap-2 rounded-control bg-brand-800 px-5 py-3 font-semibold text-white hover:bg-brand-950"
                                ><DownloadIcon class="size-5" />Descargar archivo</a
                            ></template
                        >
                        <p v-else class="text-sm text-paper-600">El archivo no está disponible por el momento.</p>
                    </div>
                </aside>
            </article>
        </Container>
    </div>
</template>
