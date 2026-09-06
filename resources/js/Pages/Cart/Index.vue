<script setup>
import { Form, Link } from '@inertiajs/vue3';
import BookCover from '@/Components/BookCover.vue';
import Button from '@/Components/Button.vue';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Container from '@/Components/Container.vue';
import { useCurrency } from '@/Composables/useCurrency';

defineProps({
    seo: { type: Object, required: true },
    cart: { type: Object, required: true },
});

const { format } = useCurrency();
</script>

<template>
    <Container class="py-10 sm:py-14 lg:py-16">
        <Breadcrumbs :items="seo.breadcrumbs" />

        <div class="max-w-3xl">
            <p class="text-xs font-semibold tracking-[0.16em] text-accent-700 uppercase">Tu selección</p>
            <h1 class="mt-2 font-serif text-3xl font-semibold text-brand-900 sm:text-4xl">Carrito</h1>
            <p class="mt-3 text-paper-600">El sistema de compra en línea estará disponible próximamente</p>
        </div>

        <div v-if="cart.items.length" class="mt-10 grid gap-8 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-start">
            <ul class="divide-y divide-paper-200 border-y border-paper-200">
                <li
                    v-for="item in cart.items"
                    :key="item.id"
                    class="grid grid-cols-[5.5rem_1fr] gap-4 py-6 sm:grid-cols-[7rem_1fr] sm:gap-6"
                >
                    <Link :href="item.href" tabindex="-1" aria-hidden="true">
                        <BookCover :title="item.title" :author="item.author" :image="item.cover" />
                    </Link>

                    <div class="min-w-0">
                        <h2 class="font-serif text-lg font-semibold text-brand-900">
                            <Link :href="item.href" class="transition-colors hover:text-accent-700">
                                {{ item.title }}
                            </Link>
                        </h2>
                        <p v-if="item.author" class="mt-1 text-sm text-paper-600">{{ item.author }}</p>
                        <p class="mt-2 font-semibold text-brand-900">{{ format(item.price) }}</p>

                        <div class="mt-4 flex flex-wrap items-start gap-3">
                            <Form
                                v-slot="{ errors, processing }"
                                :action="item.updateHref"
                                method="patch"
                                :error-bag="`cartItem${item.id}`"
                                class="flex flex-wrap items-start gap-2"
                            >
                                <div>
                                    <label :for="`quantity-${item.id}`" class="sr-only"
                                        >Cantidad de {{ item.title }}</label
                                    >
                                    <input
                                        :id="`quantity-${item.id}`"
                                        name="quantity"
                                        type="number"
                                        min="1"
                                        step="1"
                                        :value="item.quantity"
                                        class="h-10 w-20 rounded-control border border-paper-300 px-3 text-center text-sm text-brand-900"
                                        :aria-describedby="errors.quantity ? `quantity-error-${item.id}` : undefined"
                                    />
                                    <p
                                        v-if="errors.quantity"
                                        :id="`quantity-error-${item.id}`"
                                        class="mt-1 max-w-56 text-xs text-accent-700"
                                    >
                                        {{ errors.quantity }}
                                    </p>
                                </div>
                                <Button type="submit" size="sm" variant="outline" :disabled="processing"
                                    >Actualizar</Button
                                >
                            </Form>

                            <Form v-slot="{ processing }" :action="item.destroyHref" method="delete">
                                <Button type="submit" size="sm" variant="ghost" :disabled="processing">Eliminar</Button>
                            </Form>
                        </div>

                        <p class="mt-4 text-sm text-paper-600">
                            Subtotal: <span class="font-semibold text-brand-900">{{ format(item.subtotal) }}</span>
                        </p>
                    </div>
                </li>
            </ul>

            <aside class="rounded-card border border-paper-200 bg-paper-50 p-6 shadow-subtle">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="font-serif text-lg font-semibold text-brand-900">Subtotal</h2>
                    <p class="text-xl font-semibold text-brand-900">{{ format(cart.subtotal) }}</p>
                </div>
                <p class="mt-3 text-sm leading-relaxed text-paper-600">
                    Podrás completar tu compra cuando habilitemos el checkout.
                </p>
                <Button href="/libros" variant="outline" block class="mt-5">Seguir viendo libros</Button>
            </aside>
        </div>

        <div v-else class="mt-10 rounded-card border border-paper-200 bg-paper-50 px-6 py-12 text-center">
            <h2 class="font-serif text-xl font-semibold text-brand-900">Tu carrito está vacío</h2>
            <p class="mt-2 text-paper-600">Explora el catálogo y agrega los títulos que te interesen.</p>
            <Button href="/libros" class="mt-6">Ver libros</Button>
        </div>
    </Container>
</template>
