<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppFooter from '@/Components/AppFooter.vue';
import AppHeader from '@/Components/AppHeader.vue';
import FlashMessages from '@/Components/FlashMessages.vue';
import LoadingModal from '@/Components/LoadingModal.vue';
import SeoHead from '@/Components/SeoHead.vue';

/*
 * Las etiquetas de <head> se emiten aquí y no en cada página: todos los
 * controladores públicos entregan la misma prop "seo" (ver App\Support\Seo), y
 * resolverla en un solo lugar evita que una página nueva se publique sin
 * canonical, sin Open Graph o sin sus datos estructurados.
 */
const page = usePage();
const seo = computed(() => page.props.seo);
</script>

<template>
    <div class="flex min-h-full flex-col bg-white">
        <SeoHead v-if="seo" :seo="seo" />

        <a
            href="#contenido"
            class="sr-only focus:not-sr-only focus:absolute focus:top-3 focus:left-3 focus:z-50 focus:rounded-control focus:bg-brand-900 focus:px-4 focus:py-2 focus:text-sm focus:text-white"
        >
            Saltar al contenido
        </a>

        <AppHeader />

        <FlashMessages />

        <main id="contenido" class="flex-1">
            <slot />
        </main>

        <AppFooter />

        <LoadingModal />
    </div>
</template>
