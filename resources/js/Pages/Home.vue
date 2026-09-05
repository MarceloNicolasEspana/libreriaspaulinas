<script setup>
import { Head } from '@inertiajs/vue3';
import BranchCard from '@/Components/BranchCard.vue';
import Button from '@/Components/Button.vue';
import CategoryCard from '@/Components/CategoryCard.vue';
import Container from '@/Components/Container.vue';
import HeroBanner from '@/Components/HeroBanner.vue';
import NewsletterForm from '@/Components/NewsletterForm.vue';
import ProductCard from '@/Components/ProductCard.vue';
import ResourceCard from '@/Components/ResourceCard.vue';
import SectionHeader from '@/Components/SectionHeader.vue';

/*
 * Todo el contenido llega del servidor. Mientras no exista el catálogo real, su
 * origen es App\Support\DemoContent; la portada no conoce esa diferencia.
 */
defineProps({
    seo: { type: Object, required: true },
    heroBanners: { type: Array, default: () => [] },
    newReleases: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    featured: { type: Array, default: () => [] },
    resources: { type: Array, default: () => [] },
    branches: { type: Array, default: () => [] },
});
</script>

<template>
    <Head :title="seo.title">
        <meta name="description" :content="seo.description" />
    </Head>

    <!--
        Se recorren todas las campañas configuradas: HeroBanner omite las que
        están inactivas, de modo que se espera una sola visible a la vez.
    -->
    <HeroBanner v-for="banner in heroBanners" :key="banner.id" v-bind="banner" />

    <!-- Novedades -->
    <Container as="section" class="py-14 sm:py-16 lg:py-20">
        <SectionHeader
            eyebrow="Recién llegados"
            title="Novedades"
            subtitle="Los últimos títulos incorporados a nuestro fondo editorial."
        >
            <template #action>
                <Button href="/novedades" variant="link">Ver todas las novedades</Button>
            </template>
        </SectionHeader>

        <ul class="grid grid-cols-2 gap-x-5 gap-y-9 sm:grid-cols-3 sm:gap-x-6 lg:grid-cols-4">
            <li v-for="product in newReleases" :key="product.id">
                <ProductCard v-bind="product" />
            </li>
        </ul>
    </Container>

    <!-- Categorías -->
    <section class="border-y border-paper-100 bg-paper-50">
        <Container class="py-14 sm:py-16 lg:py-20">
            <SectionHeader
                eyebrow="Catálogo"
                title="Encuentra lo que necesitas"
                subtitle="Nuestro fondo reúne Sagrada Escritura, catequesis, espiritualidad y material
                    para la formación pastoral."
            />

            <ul class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4">
                <li v-for="category in categories" :key="category.href">
                    <CategoryCard v-bind="category" />
                </li>
            </ul>
        </Container>
    </section>

    <!-- Destacados -->
    <Container as="section" class="py-14 sm:py-16 lg:py-20">
        <SectionHeader
            eyebrow="Selección Paulinas"
            title="Destacados"
            subtitle="Títulos que recomendamos para acompañar la formación y la vida de fe."
        >
            <template #action>
                <Button href="/libros" variant="link">Ver todo el catálogo</Button>
            </template>
        </SectionHeader>

        <ul class="grid grid-cols-2 gap-x-5 gap-y-9 sm:gap-x-6 lg:grid-cols-4">
            <li v-for="product in featured" :key="product.id">
                <ProductCard v-bind="product" />
            </li>
        </ul>
    </Container>

    <!-- Recursos -->
    <section class="relative overflow-hidden bg-brand-950 text-white">
        <div
            class="pointer-events-none absolute -top-40 -left-24 size-[30rem] rounded-full bg-brand-700/30 blur-3xl"
            aria-hidden="true"
        />

        <Container class="relative py-14 sm:py-16 lg:py-20">
            <SectionHeader
                tone="inverse"
                eyebrow="Para la sala y la parroquia"
                title="Recursos para catequistas y profesores"
                subtitle="Material preparado por nuestro equipo pastoral para acompañar el año escolar
                    y los itinerarios de catequesis."
            />

            <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <li v-for="resource in resources" :key="resource.href">
                    <ResourceCard v-bind="resource" />
                </li>
            </ul>
        </Container>
    </section>

    <!-- Librerías -->
    <Container as="section" class="py-14 sm:py-16 lg:py-20">
        <SectionHeader
            eyebrow="Presencia en Chile"
            title="Nuestras librerías"
            subtitle="Visítanos, encarga tu pedido o consulta disponibilidad por WhatsApp."
        >
            <template #action>
                <Button href="/librerias" variant="link">Ver todas las librerías</Button>
            </template>
        </SectionHeader>

        <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <li v-for="branch in branches" :key="branch.href">
                <BranchCard v-bind="branch" />
            </li>
        </ul>

        <p class="mt-6 text-xs text-paper-600">
            Direcciones, horarios y teléfonos son referenciales y se completarán con la información oficial de cada
            librería.
        </p>
    </Container>

    <!-- Newsletter -->
    <Container as="section" class="pb-14 sm:pb-16 lg:pb-20">
        <div
            class="relative overflow-hidden rounded-card bg-brand-900 px-6 py-10 text-white sm:px-10 sm:py-12 lg:px-14"
        >
            <div
                class="pointer-events-none absolute -top-24 -right-16 size-80 rounded-full bg-gold-600/15 blur-3xl"
                aria-hidden="true"
            />

            <div class="relative grid gap-8 lg:grid-cols-2 lg:items-center lg:gap-14">
                <div>
                    <p class="text-xs font-semibold tracking-[0.16em] text-gold-200 uppercase">Newsletter</p>
                    <h2 class="mt-3 font-serif text-2xl font-semibold text-balance text-white sm:text-3xl">
                        Recibe novedades de Paulinas
                    </h2>
                    <p class="mt-3 max-w-md text-base leading-relaxed text-brand-100">
                        Lanzamientos, recursos para catequistas y descuentos, una vez al mes en tu correo.
                    </p>
                </div>

                <NewsletterForm tone="inverse" />
            </div>
        </div>
    </Container>
</template>
