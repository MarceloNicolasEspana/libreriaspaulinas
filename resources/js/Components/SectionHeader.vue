<script setup>
defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: null },
    as: { type: String, default: 'h2' },
});
</script>

<template>
    <header class="mb-8">
        <component :is="as" class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">
            {{ title }}
        </component>
        <p v-if="subtitle" class="mt-2 max-w-2xl text-base text-slate-600">
            {{ subtitle }}
        </p>
        <slot />
    </header>
</template>
