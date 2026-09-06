<script setup>
import { computed } from 'vue';
import { Form, Link } from '@inertiajs/vue3';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Button from '@/Components/Button.vue';
import Container from '@/Components/Container.vue';
import WhatsappIcon from '@/Components/Icons/WhatsappIcon.vue';
import ProductGallery from '@/Components/ProductGallery.vue';
import ProductGrid from '@/Components/ProductGrid.vue';
import SectionHeader from '@/Components/SectionHeader.vue';
import { useCurrency } from '@/Composables/useCurrency';

const props = defineProps({
    seo: { type: Object, required: true },
    product: { type: Object, required: true },
    related: { type: Array, default: () => [] },
    whatsappUrl: { type: String, required: true },
});

const { format } = useCurrency();

const states = {
    in_stock: { label: 'Disponible', text: 'text-success-700', dot: 'bg-success-600' },
    low_stock: { label: 'Últimas unidades', text: 'text-warning-700', dot: 'bg-warning-600' },
    out_of_stock: { label: 'Agotado', text: 'text-paper-600', dot: 'bg-paper-400' },
};

const state = computed(() => states[props.product.availability] ?? states.in_stock);
const authorNames = computed(() => props.product.authors.map((author) => author.name).join(', '));

const specs = computed(() =>
    [
        { label: 'Editorial', value: props.product.publisher?.name },
        { label: 'ISBN', value: props.product.isbn },
        { label: 'Páginas', value: props.product.pages },
        { label: 'Dimensiones', value: props.product.dimensions },
        { label: 'Colección', value: props.product.collection?.name },
        { label: 'Categoría', value: props.product.category?.name },
    ].filter((spec) => spec.value !== null && spec.value !== undefined),
);
</script>

<template>
    <Container class="py-10 sm:py-14 lg:py-16">
        <Breadcrumbs :items="seo.breadcrumbs" />

        <div class="grid gap-10 lg:grid-cols-[minmax(0,22rem)_minmax(0,1fr)] lg:gap-16">
            <ProductGallery
                :title="product.title"
                :author="authorNames"
                :category="product.category?.name"
                :images="product.images"
            />

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

                    <div class="mt-5 flex flex-wrap items-start gap-3">
                        <Form
                            v-slot="{ errors, processing, wasSuccessful }"
                            :action="product.cartStoreHref"
                            method="post"
                        >
                            <input type="hidden" name="quantity" value="1" />
                            <Button type="submit" :disabled="processing || !product.canAddToCart">
                                {{ product.canAddToCart ? 'Agregar al carrito' : 'Agotado' }}
                            </Button>
                            <p v-if="errors.quantity || errors.product" class="mt-2 max-w-sm text-sm text-accent-700">
                                {{ errors.quantity ?? errors.product }}
                            </p>
                            <p v-else-if="wasSuccessful" class="mt-2 text-sm text-success-700">
                                Libro agregado al carrito.
                            </p>
                        </Form>

                        <Button
                            :href="whatsappUrl"
                            external
                            variant="outline"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <template #icon><WhatsappIcon class="size-5" /></template>
                            Consultar por WhatsApp
                        </Button>
                    </div>
                </div>

                <dl v-if="specs.length" class="mt-8 grid gap-x-8 gap-y-3 sm:grid-cols-2">
                    <div
                        v-for="spec in specs"
                        :key="spec.label"
                        class="flex justify-between gap-4 border-b border-paper-100 pb-2"
                    >
                        <dt class="text-sm text-paper-600">{{ spec.label }}</dt>
                        <dd class="text-right text-sm font-medium text-brand-900">{{ spec.value }}</dd>
                    </div>
                </dl>

                <div v-if="product.description" class="mt-10">
                    <h2 class="font-serif text-xl font-semibold text-brand-900">Descripción</h2>
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
            <SectionHeader
                eyebrow="También te puede interesar"
                title="Libros relacionados"
                subtitle="Títulos de la misma categoría, colección o autor."
            />
            <ProductGrid :products="related" />
        </Container>
    </section>
</template>
