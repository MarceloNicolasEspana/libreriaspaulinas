<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
defineOptions({ layout: AdminLayout });
const props = defineProps({
    heading: { type: String, required: true },
    section: { type: String, required: true },
    recordId: { type: Number, default: null },
    fields: { type: Array, required: true },
    values: { type: Object, required: true },
    gallery: { type: Array, required: true },
    imageUrl: { type: String, default: null },
    fileName: { type: String, default: null },
    indexHref: { type: String, required: true },
    submitHref: { type: String, required: true },
});
const form = useForm({
    ...JSON.parse(JSON.stringify(props.values)),
    remove_image_ids: [],
    remove_image: false,
    remove_file: false,
});
const title = computed(() => (props.recordId ? 'Editar · ' : 'Crear · ') + props.heading);
const primary = props.fields[0].name;
const uploadVersion = ref(0);
function slugify(value) {
    return value
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '');
}
watch(
    () => form[primary],
    (value, previous) => {
        if (!props.recordId && (!form.slug || form.slug === slugify(previous || ''))) form.slug = slugify(value);
    },
);
function errorsFor(name) {
    return Object.entries(form.errors)
        .filter(([key]) => key === name || key.startsWith(name + '.'))
        .map(([, message]) => message);
}
function upload(event, field) {
    form[field.name] = field.type === 'images' ? Array.from(event.target.files) : (event.target.files[0] ?? null);
}
function submit() {
    if (
        (form.remove_image || form.remove_file || form.remove_image_ids.length) &&
        !window.confirm('Se eliminarán los archivos marcados al guardar. ¿Continuar?')
    )
        return;
    form.transform((data) => ({ ...data, _method: props.recordId ? 'put' : 'post' })).post(props.submitHref, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: (page) => {
            form.defaults({
                ...JSON.parse(JSON.stringify(page.props.values)),
                remove_image_ids: [],
                remove_image: false,
                remove_file: false,
            });
            form.reset();
            uploadVersion.value++;
        },
    });
}
function addHours() {
    form.opening_hours.push({ days: '', periods: [''] });
}
const inputClass =
    'mt-2 w-full rounded-control border border-paper-300 bg-white px-3 py-2 text-sm focus:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-200';
</script>
<template>
    <div>
        <Head :title="title" />
        <Link :href="indexHref" class="text-sm text-brand-800 underline">Volver a {{ heading.toLowerCase() }}</Link>
        <h1 class="mt-4 font-serif text-3xl">{{ title }}</h1>
        <p class="mt-2 text-sm text-paper-600">
            Completa los datos y guarda los cambios. El slug se genera desde el título y puedes editarlo.
        </p>
        <form :key="section + '-' + recordId" class="mt-7" :aria-busy="form.processing" @submit.prevent="submit">
            <div
                v-if="form.hasErrors"
                role="alert"
                class="mb-5 rounded-control bg-accent-50 p-4 text-sm text-accent-700"
            >
                Revisa los campos indicados antes de guardar.
            </div>
            <fieldset
                :disabled="form.processing"
                class="grid gap-6 rounded-card border border-paper-200 bg-white p-5 sm:grid-cols-2 sm:p-8"
            >
                <div
                    v-for="field in fields"
                    :key="field.name"
                    :class="
                        ['textarea', 'hours', 'images', 'image', 'file', 'multiselect'].includes(field.type)
                            ? 'sm:col-span-2'
                            : ''
                    "
                >
                    <label :for="field.name" class="text-sm font-semibold">{{ field.label }}</label>
                    <textarea
                        v-if="field.type === 'textarea'"
                        :id="field.name"
                        v-model="form[field.name]"
                        :rows="field.name === 'content' || field.name === 'description' ? 8 : 3"
                        :class="inputClass"
                        :aria-invalid="errorsFor(field.name).length > 0"
                    />
                    <select
                        v-else-if="field.type === 'select'"
                        :id="field.name"
                        v-model="form[field.name]"
                        :class="inputClass"
                    >
                        <option value="">Seleccionar</option>
                        <option v-for="option in field.options" :key="option.id" :value="option.id">
                            {{ option.name }}
                        </option>
                    </select>
                    <div
                        v-else-if="field.type === 'multiselect'"
                        :id="field.name"
                        class="mt-3 flex max-h-52 flex-wrap gap-4 overflow-y-auto rounded-control border border-paper-200 p-4"
                    >
                        <label v-for="option in field.options" :key="option.id" class="flex items-center gap-2 text-sm"
                            ><input v-model="form[field.name]" type="checkbox" :value="option.id" />{{
                                option.name
                            }}</label
                        >
                        <p v-if="!field.options.length" class="text-sm text-paper-600">
                            Crea autores desde el menú para poder seleccionarlos.
                        </p>
                    </div>
                    <input
                        v-else-if="field.type === 'checkbox'"
                        :id="field.name"
                        v-model="form[field.name]"
                        type="checkbox"
                        class="ml-3 size-4 align-middle"
                    />
                    <div v-else-if="['images', 'image', 'file'].includes(field.type)" class="mt-3">
                        <input
                            :id="field.name"
                            :key="uploadVersion"
                            type="file"
                            :multiple="field.type === 'images'"
                            :accept="field.type === 'file' ? '.pdf,application/pdf' : '.jpg,.jpeg,.png,.webp'"
                            class="w-full rounded-control border border-paper-200 p-3 text-sm"
                            @change="upload($event, field)"
                        />
                        <p class="mt-2 text-xs text-paper-600">
                            {{
                                field.type === 'file'
                                    ? 'PDF, hasta 20 MB. El archivo se descarga desde la ficha del recurso.'
                                    : 'JPG, PNG o WebP. Hasta 5 MB y 6000 × 6000 píxeles por imagen.'
                            }}
                        </p>
                        <template v-if="field.type === 'image' && imageUrl"
                            ><img
                                :src="imageUrl"
                                alt="Imagen actual"
                                class="mt-4 size-32 rounded-control object-contain"
                            /><label class="mt-3 flex items-center gap-2 text-sm"
                                ><input v-model="form.remove_image" type="checkbox" />Eliminar imagen actual</label
                            ></template
                        >
                        <label
                            v-if="field.type === 'file' && fileName"
                            class="mt-3 flex items-center gap-2 text-sm break-all"
                            ><input v-model="form.remove_file" type="checkbox" />Eliminar {{ fileName }}</label
                        >
                        <div
                            v-if="field.type === 'images' && gallery.length"
                            class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4"
                        >
                            <label
                                v-for="image in gallery"
                                :key="image.id"
                                class="rounded-control border border-paper-200 p-3"
                                ><img :src="image.url" :alt="image.alt" class="h-32 w-full object-contain" /><span
                                    class="mt-3 flex items-center gap-2 text-xs"
                                    ><input v-model="form.remove_image_ids" type="checkbox" :value="image.id" />Eliminar
                                    imagen</span
                                ></label
                            >
                        </div>
                    </div>
                    <div v-else-if="field.type === 'hours'" class="mt-3 flex flex-col gap-4">
                        <div
                            v-for="(hours, index) in form.opening_hours"
                            :key="index"
                            class="rounded-control border border-paper-200 p-4"
                        >
                            <label class="text-xs"
                                >Días<input v-model="hours.days" :class="inputClass" placeholder="Lunes a viernes"
                            /></label>
                            <label
                                v-for="(period, periodIndex) in hours.periods"
                                :key="periodIndex"
                                class="mt-2 block text-xs"
                                >Horario<input
                                    v-model="hours.periods[periodIndex]"
                                    :class="inputClass"
                                    placeholder="09:00–13:00"
                            /></label>
                            <div class="mt-3 flex flex-wrap gap-4 text-xs">
                                <button type="button" class="underline" @click="hours.periods.push('')">
                                    Añadir horario</button
                                ><button
                                    v-if="hours.periods.length > 1"
                                    type="button"
                                    class="underline"
                                    @click="hours.periods.pop()"
                                >
                                    Quitar último horario</button
                                ><button
                                    type="button"
                                    class="text-accent-700 underline"
                                    @click="form.opening_hours.splice(index, 1)"
                                >
                                    Quitar fila
                                </button>
                            </div>
                        </div>
                        <button type="button" class="self-start text-sm text-brand-800 underline" @click="addHours">
                            Añadir días de atención
                        </button>
                    </div>
                    <input
                        v-else
                        :id="field.name"
                        v-model="form[field.name]"
                        :type="field.type"
                        :step="['price', 'latitude', 'longitude'].includes(field.name) ? 'any' : undefined"
                        :class="inputClass"
                        :aria-invalid="errorsFor(field.name).length > 0"
                    />
                    <p
                        v-for="(error, index) in errorsFor(field.name)"
                        :key="index"
                        class="mt-2 text-sm text-accent-700"
                    >
                        {{ error }}
                    </p>
                </div>
            </fieldset>
            <div
                class="sticky bottom-0 mt-6 flex flex-wrap items-center gap-5 border-t border-paper-200 bg-paper-50 py-4"
            >
                <button
                    :disabled="form.processing"
                    class="rounded-control bg-brand-800 px-6 py-3 font-semibold text-white hover:bg-brand-950 disabled:opacity-50"
                >
                    {{ form.processing ? 'Guardando…' : 'Guardar cambios' }}
                </button>
                <Link :href="indexHref" class="text-sm text-paper-600 underline">Cancelar</Link>
                <span v-if="form.progress" role="status" class="text-sm text-paper-600"
                    >Subiendo archivos: {{ form.progress.percentage }}%</span
                >
            </div>
        </form>
    </div>
</template>
