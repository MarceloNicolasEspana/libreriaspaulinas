<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    href: { type: String, required: true },
});

const page = usePage();

const isActive = computed(() => page.url === props.href);
</script>

<template>
    <Link
        :href="href"
        :aria-current="isActive ? 'page' : undefined"
        class="border-b-2 px-1 py-4 text-sm font-medium transition-colors"
        :class="
            isActive
                ? 'border-brand-600 text-brand-700'
                : 'border-transparent text-slate-600 hover:border-slate-300 hover:text-slate-900'
        "
    >
        <slot />
    </Link>
</template>
