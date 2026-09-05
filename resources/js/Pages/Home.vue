<script setup>
import { Head } from '@inertiajs/vue3';
import HeroBanner from '@/Components/HeroBanner.vue';
import SectionHeader from '@/Components/SectionHeader.vue';
import { useInstitution } from '@/Composables/useInstitution';

defineProps({
    seo: { type: Object, required: true },
});

const { institution } = useInstitution();
</script>

<template>
    <Head :title="seo.title">
        <meta name="description" :content="seo.description" />
    </Head>

    <HeroBanner :title="institution.shortName" :subtitle="institution.tagline" />

    <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        <SectionHeader
            title="Sitio en construcción"
            subtitle="La arquitectura base del proyecto está lista. Las secciones de catálogo,
                librerías y recursos se incorporarán en las siguientes fases."
        />
    </div>
</template>
