<script setup>
import { onUnmounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLogo from '@/Components/AppLogo.vue';
import { subscribeToPageLoading } from '@/Composables/subscribeToPageLoading';

const isLoading = ref(false);

onUnmounted(
    subscribeToPageLoading(router, (loading) => {
        isLoading.value = loading;
    }),
);
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-150 ease-out"
            leave-active-class="transition-opacity duration-150 ease-in"
            enter-from-class="opacity-0"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isLoading"
                class="fixed inset-0 z-100 flex items-center justify-center bg-brand-950/45 px-6 backdrop-blur-[2px]"
                role="status"
                aria-live="polite"
                aria-label="Cargando contenido"
                aria-busy="true"
            >
                <div class="flex flex-col items-center gap-5">
                    <div class="relative">
                        <span
                            aria-hidden="true"
                            class="absolute inset-0 animate-ping rounded-full bg-accent-200/40 [animation-duration:2s] motion-reduce:animate-none"
                        />

                        <div
                            class="relative flex size-36 animate-loading-heartbeat items-center justify-center rounded-full border border-white/80 bg-white shadow-raised motion-reduce:animate-none sm:size-40"
                        >
                            <AppLogo
                                variant="mark"
                                aria-hidden="true"
                                class="animate-loading-spin motion-reduce:animate-none [&_img]:size-20 sm:[&_img]:size-24"
                            />
                        </div>
                    </div>

                    <p class="flex items-end font-serif text-xl font-semibold tracking-wide text-white drop-shadow-sm">
                        <span>Cargando</span>
                        <span aria-hidden="true" class="ml-1 flex h-6 items-end gap-1">
                            <span class="size-1.5 animate-bounce rounded-full bg-white [animation-delay:-0.3s]" />
                            <span class="size-1.5 animate-bounce rounded-full bg-white [animation-delay:-0.15s]" />
                            <span class="size-1.5 animate-bounce rounded-full bg-white" />
                        </span>
                    </p>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
