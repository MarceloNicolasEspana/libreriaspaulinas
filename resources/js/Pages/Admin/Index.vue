<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import EmptyState from '@/Components/EmptyState.vue';
defineOptions({ layout: AdminLayout });
const props = defineProps({
    section: { type: String, required: true },
    heading: { type: String, required: true },
    deactivate: Boolean,
    records: { type: Object, required: true },
    createHref: { type: String, required: true },
});
const busy = ref(null);
function remove(record) {
    const message = props.deactivate
        ? 'Se desactivará "' + record.title + '". Podrás volver a activarlo desde Editar.'
        : '¿Eliminar "' + record.title + '"? Esta acción no se puede deshacer.';
    if (!window.confirm(message)) return;
    busy.value = record.id;
    router.delete(record.deleteHref, {
        data: { confirmed: true },
        preserveScroll: true,
        onFinish: () => (busy.value = null),
    });
}
</script>
<template>
    <div>
        <Head :title="heading + ' · Administración'" />
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="font-serif text-3xl">{{ heading }}</h1>
                <p class="mt-2 text-sm text-paper-600">{{ records.total }} registros</p>
            </div>
            <Link
                :href="createHref"
                class="rounded-control bg-brand-800 px-5 py-3 text-sm font-semibold text-white hover:bg-brand-950"
                >Crear registro</Link
            >
        </div>
        <div v-if="records.data.length" class="mt-7 overflow-x-auto rounded-card border border-paper-200 bg-white">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-paper-200 bg-paper-50 text-paper-600">
                    <tr>
                        <th class="px-5 py-4">Nombre / título</th>
                        <th v-if="deactivate" class="px-5 py-4">Estado</th>
                        <th class="px-5 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-paper-100">
                    <tr v-for="record in records.data" :key="record.id">
                        <td class="min-w-48 px-5 py-4">
                            <Link :href="record.editHref" class="font-semibold hover:underline">{{
                                record.title
                            }}</Link>
                            <p class="mt-1 text-xs break-all text-paper-600">{{ record.slug }}</p>
                        </td>
                        <td v-if="deactivate" class="px-5 py-4">
                            <span
                                class="rounded-full px-2 py-1 text-xs"
                                :class="
                                    record.active ? 'bg-success-50 text-success-700' : 'bg-paper-100 text-paper-600'
                                "
                                >{{ record.active ? 'Activo' : 'Inactivo' }}</span
                            >
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex flex-wrap justify-end gap-4">
                                <Link :href="record.editHref" class="font-semibold text-brand-800 underline"
                                    >Editar</Link
                                ><button
                                    v-if="!deactivate || record.active"
                                    :disabled="busy !== null"
                                    class="text-accent-700 underline disabled:opacity-50"
                                    @click="remove(record)"
                                >
                                    {{ busy === record.id ? 'Procesando…' : deactivate ? 'Desactivar' : 'Eliminar' }}
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <EmptyState
            v-else
            class="mt-7"
            title="Aún no hay registros"
            description="Crea el primer registro para comenzar."
        />
        <Pagination :links="records.links" />
    </div>
</template>
