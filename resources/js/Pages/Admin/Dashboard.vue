<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
defineOptions({ layout: AdminLayout });
defineProps({ stats: { type: Array, required: true } });
</script>
<template>
    <div>
        <Head title="Administración" />
        <p class="text-sm font-semibold tracking-widest text-accent-700 uppercase">Tu espacio de trabajo</p>
        <h1 class="mt-2 font-serif text-4xl">Resumen del sitio</h1>
        <p class="mt-3 text-paper-600">
            Gestiona el catálogo, las librerías y los contenidos. Los totales incluyen registros activos y borradores.
        </p>
        <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <Link
                v-for="stat in stats"
                :key="stat.href"
                :href="stat.href"
                class="flex items-center justify-between rounded-card border border-paper-200 bg-white p-6 hover:border-brand-400"
                ><span class="font-semibold">{{ stat.label }}</span
                ><span class="font-serif text-4xl text-brand-800">{{ stat.total }}</span></Link
            >
        </div>
    </div>
</template>
