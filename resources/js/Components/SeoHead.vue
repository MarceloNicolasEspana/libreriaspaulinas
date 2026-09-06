<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';

/*
 * Los datos estructurados (JSON-LD) no se emiten aquí: el compilador de Vue
 * descarta las etiquetas <script> dentro de una plantilla. Se imprimen en el
 * documento raíz (resources/views/app.blade.php), que además es lo que lee un
 * rastreador cuando pide la URL directamente.
 */
const props = defineProps({ seo: { type: Object, required: true } });
const page = usePage();
const siteName = computed(() => page.props.institution?.shortName ?? 'Paulinas');
const socialTitle = computed(() =>
    props.seo.title === 'Inicio' ? `${siteName.value} Chile` : `${props.seo.title} · ${siteName.value}`,
);
</script>

<template>
    <Head :title="seo.title">
        <meta name="description" :content="seo.description" />
        <link rel="canonical" :href="seo.canonical" />
        <meta name="robots" :content="seo.noindex ? 'noindex,follow' : 'index,follow'" />

        <meta property="og:locale" content="es_CL" />
        <meta property="og:site_name" :content="siteName" />
        <meta property="og:type" :content="seo.type ?? 'website'" />
        <meta property="og:title" :content="socialTitle" />
        <meta property="og:description" :content="seo.description" />
        <meta property="og:url" :content="seo.canonical" />
        <meta v-if="seo.image" property="og:image" :content="seo.image" />
        <meta v-if="seo.image" property="og:image:alt" :content="seo.title" />

        <meta name="twitter:card" :content="seo.image ? 'summary_large_image' : 'summary'" />
        <meta name="twitter:title" :content="socialTitle" />
        <meta name="twitter:description" :content="seo.description" />
        <meta v-if="seo.image" name="twitter:image" :content="seo.image" />
    </Head>
</template>
