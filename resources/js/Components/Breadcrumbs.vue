<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({ items: { type: Array, default: () => [] } });
</script>

<template>
    <nav v-if="items.length > 1" class="mb-8 text-sm text-paper-600" aria-label="Migas de pan">
        <ol class="flex flex-wrap items-center gap-x-2 gap-y-1">
            <li v-for="(item, index) in items" :key="item.href" class="flex items-center gap-2">
                <span v-if="index" aria-hidden="true">/</span>
                <Link v-if="index < items.length - 1" :href="item.href" class="hover:text-accent-700 hover:underline">
                    {{ item.name }}
                </Link>
                <span v-else aria-current="page" class="text-paper-800">{{ item.name }}</span>
            </li>
        </ol>
    </nav>
</template>
