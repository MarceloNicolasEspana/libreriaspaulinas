<script setup>
import AppFooter from '@/Components/AppFooter.vue';
import AppHeader from '@/Components/AppHeader.vue';
import FlashMessages from '@/Components/FlashMessages.vue';
import LoadingModal from '@/Components/LoadingModal.vue';
</script>

<template>
    <div class="flex min-h-full flex-col bg-white">
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
