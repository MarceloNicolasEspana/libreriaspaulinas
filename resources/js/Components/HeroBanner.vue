<script setup>
defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: null },
});
</script>

<template>
    <section class="bg-brand-700 text-white">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
            <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">
                {{ title }}
            </h1>
            <p v-if="subtitle" class="mt-4 max-w-2xl text-lg text-brand-100">
                {{ subtitle }}
            </p>
            <div v-if="$slots.actions" class="mt-8 flex flex-wrap gap-3">
                <slot name="actions" />
            </div>
        </div>
    </section>
</template>
