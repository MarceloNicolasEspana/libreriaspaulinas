<script setup>
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Container from '@/Components/Container.vue';

defineProps({ seo: { type: Object, required: true }, sources: { type: Array, default: () => [] } });
</script>

<template>
    <div>
        <Container class="py-14 sm:py-20">
            <Breadcrumbs :items="seo.breadcrumbs" />
            <article class="mx-auto max-w-4xl">
                <header class="max-w-3xl">
                    <p class="text-sm font-semibold tracking-widest text-accent-700 uppercase">Identidad paulina</p>
                    <h1 class="mt-4 font-serif text-4xl text-brand-950 sm:text-6xl">Quiénes somos</h1>
                    <p class="mt-6 text-xl leading-relaxed text-paper-700">
                        Las Hijas de San Pablo anuncian el Evangelio en la cultura de la comunicación, uniendo fe,
                        formación y servicio.
                    </p>
                </header>

                <div class="mt-12 grid gap-8 md:grid-cols-2">
                    <section class="rounded-card bg-brand-950 p-7 text-white sm:p-9">
                        <h2 class="font-serif text-2xl text-white">Una misión en diálogo con la cultura</h2>
                        <p class="mt-4 leading-relaxed text-brand-100">
                            La misión paulina acompaña a las personas mediante libros, recursos pastorales y los
                            lenguajes de la comunicación. Cada obra busca abrir un espacio para la reflexión, el
                            encuentro y la esperanza.
                        </p>
                    </section>
                    <section id="nuestra-mision" class="rounded-card border border-paper-200 bg-paper-50 p-7 sm:p-9">
                        <h2 class="font-serif text-2xl">Nuestra presencia en Chile</h2>
                        <p class="mt-4 leading-relaxed text-paper-700">
                            Paulinas Chile ofrece un fondo editorial y espacios de atención para apoyar la
                            evangelización, la educación religiosa y la formación de comunidades.
                        </p>
                    </section>
                </div>

                <section v-if="sources.length" class="mt-12 border-t border-paper-200 pt-8">
                    <h2 class="font-serif text-xl">Fuentes institucionales</h2>
                    <ul class="mt-4 flex flex-col gap-3 text-sm">
                        <li v-for="source in sources" :key="source.href">
                            <a
                                :href="source.href"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-brand-800 underline underline-offset-4"
                            >
                                {{ source.label }} <span class="sr-only">(abre en una pestaña nueva)</span>
                            </a>
                        </li>
                    </ul>
                </section>
            </article>
        </Container>
    </div>
</template>
