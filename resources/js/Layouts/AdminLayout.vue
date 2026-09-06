<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import FlashMessages from '@/Components/FlashMessages.vue';
const page = usePage();
</script>
<template>
    <div class="min-h-screen bg-paper-50 text-brand-950 lg:flex">
        <!--
            El panel no debe llegar nunca a un buscador. robots.txt ya lo pide,
            pero eso solo vale para el rastreo: la etiqueta cierra también la
            indexación de una URL que se descubra por otro camino.
        -->
        <Head>
            <meta name="robots" content="noindex,nofollow" />
        </Head>

        <aside class="hidden w-60 shrink-0 border-r border-paper-200 bg-white lg:block">
            <div class="sticky top-0 flex max-h-screen flex-col gap-6 overflow-y-auto p-6">
                <Link href="/admin" class="font-serif text-2xl font-semibold"
                    >Paulinas
                    <span class="block font-sans text-xs font-normal tracking-widest text-paper-600 uppercase"
                        >Administración</span
                    ></Link
                >
                <nav aria-label="Administración" class="flex flex-col gap-1">
                    <Link
                        v-for="item in page.props.adminNavigation"
                        :key="item.href"
                        :href="item.href"
                        class="rounded-control px-3 py-2 text-sm hover:bg-brand-50"
                        :class="page.url.split('?')[0] === item.href ? 'bg-brand-100 font-semibold' : ''"
                        >{{ item.label }}</Link
                    >
                </nav>
                <Link href="/" class="text-sm text-paper-600 underline">Ver sitio público</Link>
            </div>
        </aside>
        <div class="min-w-0 flex-1">
            <header
                class="flex flex-wrap items-center justify-between gap-3 border-b border-paper-200 bg-white px-5 py-4 sm:px-8"
            >
                <details class="lg:hidden">
                    <summary class="cursor-pointer font-semibold">Menú del panel</summary>
                    <nav aria-label="Administración móvil" class="mt-3 flex flex-col gap-2">
                        <Link
                            v-for="item in page.props.adminNavigation"
                            :key="item.href"
                            :href="item.href"
                            class="py-1 text-sm"
                            >{{ item.label }}</Link
                        ><Link href="/">Ver sitio</Link>
                    </nav>
                </details>
                <p class="text-sm text-paper-600">{{ page.props.auth.user?.name }}</p>
                <Link
                    :href="page.props.logoutHref"
                    method="post"
                    as="button"
                    class="text-sm font-semibold underline underline-offset-4"
                    >Cerrar sesión</Link
                >
            </header>
            <FlashMessages />
            <main class="mx-auto max-w-7xl px-5 py-8 sm:px-8"><slot /></main>
        </div>
    </div>
</template>
