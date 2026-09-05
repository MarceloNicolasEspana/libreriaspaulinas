<script setup>
import { Link } from '@inertiajs/vue3';
import NavLink from '@/Components/NavLink.vue';
import { useInstitution } from '@/Composables/useInstitution';

const { institution, fullAddress } = useInstitution();

// Las secciones del sitio (catálogo, librerías, recursos) se irán agregando
// aquí a medida que existan sus rutas.
const navigation = [{ label: 'Inicio', href: '/' }];

const currentYear = new Date().getFullYear();
</script>

<template>
    <div class="flex min-h-full flex-col">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-6 px-4 sm:px-6 lg:px-8">
                <Link href="/" class="flex flex-col py-3">
                    <span class="text-xl font-semibold tracking-tight text-brand-700">
                        {{ institution.shortName }}
                    </span>
                    <span class="text-xs text-slate-500">{{ institution.tagline }}</span>
                </Link>

                <nav class="flex items-center gap-6" aria-label="Navegación principal">
                    <NavLink v-for="item in navigation" :key="item.href" :href="item.href">
                        {{ item.label }}
                    </NavLink>
                </nav>
            </div>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <footer class="mt-16 border-t border-slate-200 bg-slate-50">
            <div class="mx-auto grid max-w-6xl gap-8 px-4 py-12 sm:px-6 md:grid-cols-3 lg:px-8">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">{{ institution.legalName }}</h2>
                    <p class="mt-2 text-sm text-slate-600">{{ fullAddress }}</p>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Contacto</h2>
                    <ul class="mt-2 space-y-1 text-sm text-slate-600">
                        <li>{{ institution.contact.phone }}</li>
                        <li>
                            <a class="hover:text-brand-700" :href="`mailto:${institution.contact.sales_email}`">
                                {{ institution.contact.sales_email }}
                            </a>
                        </li>
                        <li>
                            <a class="hover:text-brand-700" :href="`mailto:${institution.contact.distribution_email}`">
                                {{ institution.contact.distribution_email }}
                            </a>
                        </li>
                    </ul>
                </div>

                <div v-if="Object.keys(institution.social).length">
                    <h2 class="text-sm font-semibold text-slate-900">Síguenos</h2>
                    <ul class="mt-2 space-y-1 text-sm text-slate-600">
                        <li v-for="(url, network) in institution.social" :key="network">
                            <a class="capitalize hover:text-brand-700" :href="url" rel="noopener" target="_blank">
                                {{ network }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-slate-200 py-4 text-center text-xs text-slate-500">
                © {{ currentYear }} {{ institution.legalName }}
            </div>
        </footer>
    </div>
</template>
