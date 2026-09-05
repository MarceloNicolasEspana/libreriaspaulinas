<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import BookIcon from '@/Components/Icons/BookIcon.vue';
import CalendarIcon from '@/Components/Icons/CalendarIcon.vue';
import ChevronRightIcon from '@/Components/Icons/ChevronRightIcon.vue';
import ClipboardIcon from '@/Components/Icons/ClipboardIcon.vue';
import DownloadIcon from '@/Components/Icons/DownloadIcon.vue';
import GraduationCapIcon from '@/Components/Icons/GraduationCapIcon.vue';
import HeartIcon from '@/Components/Icons/HeartIcon.vue';

const props = defineProps({
    title: { type: String, required: true },
    description: { type: String, default: null },
    href: { type: String, required: true },
    /** Clave del icono; el backend no conoce componentes Vue. */
    icon: { type: String, default: 'book' },
});

const icons = {
    book: BookIcon,
    calendar: CalendarIcon,
    clipboard: ClipboardIcon,
    download: DownloadIcon,
    graduation: GraduationCapIcon,
    heart: HeartIcon,
};

const iconComponent = computed(() => icons[props.icon] ?? BookIcon);
</script>

<template>
    <Link
        :href="href"
        class="group flex h-full flex-col rounded-card border border-white/10 bg-white/5 p-5 transition-colors duration-150 hover:border-gold-400/40 hover:bg-white/10 sm:p-6"
    >
        <span
            class="grid size-11 shrink-0 place-items-center rounded-control bg-gold-600/20 text-gold-200 transition-colors duration-150 group-hover:bg-gold-600/30"
            aria-hidden="true"
        >
            <component :is="iconComponent" class="size-5.5" />
        </span>

        <h3 class="mt-4 font-serif text-lg font-semibold text-white">{{ title }}</h3>

        <p v-if="description" class="mt-2 flex-1 text-sm leading-relaxed text-brand-100">{{ description }}</p>

        <span class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-gold-200">
            Ver recursos
            <ChevronRightIcon class="size-3.5 transition-transform duration-150 group-hover:translate-x-0.5" />
        </span>
    </Link>
</template>
