<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import BookCover from '@/Components/BookCover.vue';
import Button from '@/Components/Button.vue';
import Container from '@/Components/Container.vue';
import ProductGrid from '@/Components/ProductGrid.vue';
import SectionHeader from '@/Components/SectionHeader.vue';
import { useCurrency } from '@/Composables/useCurrency';

const props = defineProps({
    seo: { type: Object, required: true },
    product: { type: Object, required: true },
    related: { type: Array, default: () => [] },
});

const { format } = useCurrency();

// Mismos estados y colores que la tarjeta del catálogo.
const states = {
    in_stock: { label: 'Disponible en librería', text: 'text-success-700', dot: 'bg-success-600' },
    low_stock: { label: 'Últimas unidades', text: 'text-warning-700', dot: 'bg-warning-600' },
    out_of_stock: { label: 'Bajo pedido', text: 'text-paper-600', dot: 'bg-paper-400' },
};

const state = computed(() => states[props.product.availability] ?? states.in_stock);

const cover = computed(() => props.product.images[0] ?? null);

const authorNames = computed(() => props.product.authors.map((author) => author.name).join(', '));

/*
 * Ficha técnica. Se arma como lista de pares para no repetir marcado por cada
 * dato y para omitir en un solo lugar los que vienen vacíos.
 */
const specs = computed(() =>
    [
        { label: 'ISBN', value: props.product.isbn },
        { label: 'Editorial', value: props.product.publisher?.name },
        { label: 'Colección', value: props.product.collection?.name },
        { label: 'Páginas', value: props.product.pages },
        { label: 'Formato', value: props.product.dimensions },
        { label: 'Publicación', value: props.product.publishedAt },
    ].filter((spec) => spec.value !== null && spec.value !== undefined),
);
</script>

<template>
    <Head :title="seo.title">
        <meta name="description" :content="seo.description" />
    </Head>

    <Container class="py-10 sm:py-14 lg:py-16">
        <!-- Migas: sección padre e hija, cuando existen. -->
        <nav v-if="product.category" class="mb-8 text-sm text-paper-600" aria-label="Migas de pan">
            <Link href="/libros" class="transition-colors hover:text-accent-700">Libros</Link>
            <template v-if="product.category.parent">
                <span aria-hidden="true"> · </span>
                <Link :href="product.category.parent.href" class="transition-colors hover:text-accent-700">
                    {{ product.category.parent.name }}
                </Link>
            </template>
            <span aria-hidden="true"> · </span>
            <Link :href="product.category.href" class="transition-colors hover:text-accent-700">
                {{ product.category.name }}
            </Link>
        </nav>

        <div class="grid gap-10 lg:grid-cols-[minmax(0,22rem)_minmax(0,1fr)] lg:gap-16">
            <div>
                <BookCover
                    :title="product.title"
                    :author="authorNames"
                    :category="product.category?.name"
                    :image="cover?.path"
                />
            </div>

            <div>
                <h1 class="font-serif text-2xl leading-tight font-semibold text-brand-900 sm:text-3xl">
                    {{ product.title }}
                </h1>

                <p v-if="product.authors.length" class="mt-3 text-paper-700">
                    <template v-for="(author, index) in product.authors" :key="author.href">
                        <span v-if="index > 0">, </span>
                        <Link :href="author.href" class="transition-colors hover:text-accent-700">
                            {{ author.name }}
                        </Link>
                    </template>
                </p>

                <p v-if="product.shortDescription" class="mt-6 text-lg leading-relaxed text-paper-700">
                    {{ product.shortDescription }}
                </p>

                <div class="mt-8 rounded-card border border-paper-200 bg-paper-50 p-6">
                    <p class="font-serif text-3xl font-semibold text-brand-900">{{ format(product.price) }}</p>

                    <p class="mt-2 flex items-center gap-1.5 text-sm" :class="state.text">
                        <span class="size-2 shrink-0 rounded-full" :class="state.dot" aria-hidden="true" />
                        {{ state.label }}
                    </p>

                    <!--
                        La venta en línea llega en una etapa posterior; hasta
                        entonces la ficha deriva a la librería.
                    -->
                    <div class="mt-5 flex flex-wrap gap-3">
                        <Button href="/contacto">Consultar disponibilidad</Button>
                        <Button href="/librerias" variant="outline">Ver nuestras librerías</Button>
                    </div>
                </div>

                <dl v-if="specs.length" class="mt-8 grid gap-x-8 gap-y-3 sm:grid-cols-2">
                    <div
                        v-for="spec in specs"
                        :key="spec.label"
                        class="flex justify-between gap-4 border-b border-paper-100 pb-2"
                    >
                        <dt class="text-sm text-paper-600">{{ spec.label }}</dt>
                        <dd class="text-sm font-medium text-brand-900">{{ spec.value }}</dd>
                    </div>
                </dl>

                <div v-if="product.description" class="mt-10">
                    <h2 class="font-serif text-xl font-semibold text-brand-900">Sobre este título</h2>
                    <!-- El texto llega en párrafos separados por línea en blanco. -->
                    <p
                        v-for="(paragraph, index) in product.description.split('\n\n')"
                        :key="index"
                        class="mt-4 leading-relaxed text-paper-700"
                    >
                        {{ paragraph }}
                    </p>
                </div>
            </div>
        </div>
    </Container>

    <section v-if="related.length" class="border-t border-paper-100 bg-paper-50">
        <Container class="py-14 sm:py-16">
            <SectionHeader eyebrow="También te puede interesar" title="En la misma sección" />
            <ProductGrid :products="related" />
        </Container>
    </section>
</template>
