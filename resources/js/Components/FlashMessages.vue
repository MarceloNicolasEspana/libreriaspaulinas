<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';

const page = usePage();
const flash = computed(() => page.props.flash ?? {});
</script>

<template>
    <Container v-if="flash.success || flash.error" class="pt-4" aria-live="polite">
        <p
            class="rounded-control border px-4 py-3 text-sm"
            :class="
                flash.error
                    ? 'border-accent-200 bg-accent-50 text-accent-800'
                    : 'border-success-600/25 bg-success-50 text-success-700'
            "
        >
            {{ flash.error ?? flash.success }}
        </p>
    </Container>
</template>
