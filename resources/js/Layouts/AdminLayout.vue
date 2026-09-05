<script setup>
import { Link } from '@inertiajs/vue3';
import { useInstitution } from '@/Composables/useInstitution';

const { institution } = useInstitution();

// Estructura preparada para el panel administrativo. Los ítems del menú se
// agregarán junto con sus rutas y políticas de acceso en la fase de admin.
const navigation = [];
</script>

<template>
    <div class="flex min-h-full bg-slate-100">
        <aside class="hidden w-64 shrink-0 border-r border-slate-200 bg-white lg:block">
            <div class="border-b border-slate-200 px-6 py-4">
                <Link href="/" class="text-sm font-semibold text-brand-700">
                    {{ institution.shortName }}
                </Link>
                <p class="text-xs text-slate-500">Administración</p>
            </div>

            <nav v-if="navigation.length" class="space-y-1 p-4" aria-label="Navegación del panel">
                <Link
                    v-for="item in navigation"
                    :key="item.href"
                    :href="item.href"
                    class="block rounded px-3 py-2 text-sm text-slate-700 hover:bg-slate-100"
                >
                    {{ item.label }}
                </Link>
            </nav>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="border-b border-slate-200 bg-white px-6 py-4">
                <h1 class="text-lg font-semibold text-slate-900">
                    <slot name="header">Panel</slot>
                </h1>
            </header>

            <main class="flex-1 p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
