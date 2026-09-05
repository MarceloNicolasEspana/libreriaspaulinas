<script setup>
import { computed, ref } from 'vue';
import Button from '@/Components/Button.vue';

const props = defineProps({
    /** "inverse" para el formulario sobre fondo oscuro. */
    tone: {
        type: String,
        default: 'default',
        validator: (value) => ['default', 'inverse'].includes(value),
    },
});

/*
 * Solo interfaz en esta fase: todavía no existe el endpoint de suscripción. La
 * validación la hace el navegador y el envío se resuelve en el cliente; cuando
 * exista el backend, este componente pasa a usar useForm de Inertia.
 */
const email = ref('');
const subscribed = ref(false);

const submit = () => {
    subscribed.value = true;
    email.value = '';
};

const isInverse = computed(() => props.tone === 'inverse');
</script>

<template>
    <form class="w-full" @submit.prevent="submit">
        <div class="flex flex-col gap-3 sm:flex-row">
            <label for="newsletter-email" class="sr-only">Correo electrónico</label>
            <input
                id="newsletter-email"
                v-model="email"
                type="email"
                required
                autocomplete="email"
                placeholder="tu@correo.cl"
                class="h-13 w-full min-w-0 flex-1 rounded-control border bg-white px-4 text-base text-brand-900 placeholder:text-paper-500 sm:text-sm"
                :class="isInverse ? 'border-transparent' : 'border-brand-200'"
            />
            <Button type="submit" size="lg" class="shrink-0">Suscribirme</Button>
        </div>

        <p
            v-if="subscribed"
            role="status"
            class="mt-3 text-sm font-medium"
            :class="isInverse ? 'text-gold-200' : 'text-success-700'"
        >
            ¡Gracias! Pronto recibirás nuestras novedades.
        </p>

        <p class="mt-3 text-xs leading-relaxed" :class="isInverse ? 'text-brand-200' : 'text-paper-600'">
            Usaremos tu correo solo para enviarte novedades de Paulinas. Puedes darte de baja cuando quieras.
        </p>
    </form>
</template>
