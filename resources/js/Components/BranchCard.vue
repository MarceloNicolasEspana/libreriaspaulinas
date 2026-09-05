<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import ChevronRightIcon from '@/Components/Icons/ChevronRightIcon.vue';
import ClockIcon from '@/Components/Icons/ClockIcon.vue';
import MapPinIcon from '@/Components/Icons/MapPinIcon.vue';
import PhoneIcon from '@/Components/Icons/PhoneIcon.vue';
import WhatsappIcon from '@/Components/Icons/WhatsappIcon.vue';

const props = defineProps({
    name: { type: String, required: true },
    city: { type: String, required: true },
    address: { type: String, required: true },
    hours: { type: String, required: true },
    phone: { type: String, default: null },
    whatsapp: { type: String, default: null },
    href: { type: String, required: true },
});

// wa.me y tel: no admiten espacios ni signos de formato.
const digitsOnly = (value) => value.replace(/\D/g, '');

const whatsappUrl = computed(() => `https://wa.me/${digitsOnly(props.whatsapp ?? '')}`);
const phoneUrl = computed(() => `tel:+${digitsOnly(props.phone ?? '')}`);

// Sin coordenadas todavía, la ruta se resuelve buscando la dirección.
const mapUrl = computed(
    () => `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(`${props.address}, ${props.city}`)}`,
);
</script>

<template>
    <article class="flex h-full flex-col rounded-card border border-paper-200 bg-white p-5 shadow-subtle sm:p-6">
        <p class="text-xs font-semibold tracking-[0.14em] text-accent-700 uppercase">{{ city }}</p>
        <h3 class="mt-1.5 font-serif text-xl font-semibold">{{ name }}</h3>

        <ul class="mt-4 flex-1 space-y-3 text-sm text-paper-700">
            <li class="flex gap-2.5">
                <MapPinIcon class="mt-0.5 size-4.5 shrink-0 text-brand-500" />
                <span>{{ address }}</span>
            </li>
            <li class="flex gap-2.5">
                <ClockIcon class="mt-0.5 size-4.5 shrink-0 text-brand-500" />
                <span>{{ hours }}</span>
            </li>
            <li v-if="phone" class="flex gap-2.5">
                <PhoneIcon class="mt-0.5 size-4.5 shrink-0 text-brand-500" />
                <a :href="phoneUrl" class="transition-colors hover:text-accent-700">{{ phone }}</a>
            </li>
        </ul>

        <a
            v-if="whatsapp"
            :href="whatsappUrl"
            target="_blank"
            rel="noopener"
            class="mt-5 inline-flex h-10 items-center justify-center gap-2 rounded-control border border-brand-200 text-sm font-medium text-brand-800 transition-colors hover:border-brand-300 hover:bg-brand-50"
        >
            <WhatsappIcon class="size-4.5" />
            Escribir por WhatsApp
        </a>

        <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 border-t border-paper-100 pt-4">
            <Link
                :href="href"
                class="group inline-flex items-center gap-1 text-sm font-medium text-accent-700 transition-colors hover:text-accent-800"
            >
                Ver librería
                <ChevronRightIcon class="size-3.5 transition-transform duration-150 group-hover:translate-x-0.5" />
            </Link>

            <a
                :href="mapUrl"
                target="_blank"
                rel="noopener"
                class="text-sm font-medium text-paper-600 underline decoration-paper-300 underline-offset-4 transition-colors hover:text-brand-800"
            >
                Cómo llegar
            </a>
        </div>
    </article>
</template>
