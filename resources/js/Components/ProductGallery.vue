<script setup>
import { computed, ref, watch } from 'vue';
import BookCover from '@/Components/BookCover.vue';

const props = defineProps({
    title: { type: String, required: true },
    author: { type: String, default: null },
    category: { type: String, default: null },
    images: { type: Array, default: () => [] },
});

const selectedIndex = ref(0);
const selectedImage = computed(() => props.images[selectedIndex.value] ?? null);

watch(
    () => props.images,
    () => {
        selectedIndex.value = 0;
    },
);
</script>

<template>
    <div>
        <div v-if="selectedImage" class="aspect-[3/4] overflow-hidden rounded-card bg-paper-50 shadow-card">
            <img :src="selectedImage.path" :alt="selectedImage.alt" class="size-full object-contain" />
        </div>

        <BookCover v-else :title="title" :author="author" :category="category" />

        <div v-if="images.length > 1" class="mt-4 grid grid-cols-4 gap-3" aria-label="Galería del libro">
            <button
                v-for="(image, index) in images"
                :key="image.path"
                type="button"
                class="aspect-[3/4] overflow-hidden rounded-control border bg-paper-50 transition-colors"
                :class="index === selectedIndex ? 'border-accent-600 ring-1 ring-accent-600' : 'border-paper-200'"
                :aria-label="`Ver imagen ${index + 1} de ${title}`"
                :aria-pressed="index === selectedIndex"
                @click="selectedIndex = index"
            >
                <img :src="image.path" :alt="image.alt" class="size-full object-cover" />
            </button>
        </div>
    </div>
</template>
