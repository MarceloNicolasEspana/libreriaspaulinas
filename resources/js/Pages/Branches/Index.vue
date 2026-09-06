<script setup>
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import BranchCard from '@/Components/BranchCard.vue';
import Container from '@/Components/Container.vue';
import EmptyState from '@/Components/EmptyState.vue';

defineProps({ seo: { type: Object, required: true }, branches: { type: Array, required: true } });
</script>

<template>
    <div>
        <Container class="py-14 sm:py-20">
            <Breadcrumbs :items="seo.breadcrumbs" />
            <header class="max-w-3xl">
                <p class="text-sm font-semibold tracking-widest text-accent-700 uppercase">Presencia en Chile</p>
                <h1 class="mt-3 font-serif text-4xl text-brand-950 sm:text-5xl">Nuestras librerías</h1>
                <p class="mt-5 text-lg leading-relaxed text-paper-600">
                    Encuentra direcciones, horarios y formas de contacto de cada librería Paulinas.
                </p>
            </header>
            <ul v-if="branches.length" class="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                <li v-for="branch in branches" :key="branch.id"><BranchCard v-bind="branch" /></li>
            </ul>
            <EmptyState
                v-else
                class="mt-10"
                title="No hay librerías disponibles"
                description="Vuelve a consultar más adelante o escríbenos desde la página de contacto."
            />
        </Container>
    </div>
</template>
