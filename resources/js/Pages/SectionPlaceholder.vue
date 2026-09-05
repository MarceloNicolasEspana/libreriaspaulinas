<script setup>
import { Head } from '@inertiajs/vue3';
import Button from '@/Components/Button.vue';
import Container from '@/Components/Container.vue';
import SectionHeader from '@/Components/SectionHeader.vue';

defineProps({
    seo: { type: Object, required: true },
    section: { type: String, required: true },
});
</script>

<template>
    <Head :title="seo.title">
        <meta name="description" :content="seo.description" />
    </Head>

    <Container class="py-20">
        <SectionHeader
            :eyebrow="section"
            title="Esta sección está en preparación"
            subtitle="El contenido de esta sección se incorporará en una próxima etapa del proyecto.
                Mientras tanto, puedes escribirnos o visitar el inicio."
        />

        <div class="flex flex-wrap gap-3">
            <Button href="/">Volver al inicio</Button>
            <Button href="/contacto" variant="outline">Contactar</Button>
        </div>
    </Container>
</template>
