<script setup>
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Container from '@/Components/Container.vue';

defineProps({ seo: { type: Object, required: true }, branch: { type: Object, required: true } });
</script>

<template>
    <div>
        <Container class="py-14 sm:py-20">
            <Breadcrumbs :items="seo.breadcrumbs" />
            <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_22rem]">
                <section>
                    <p class="text-sm font-semibold tracking-widest text-accent-700 uppercase">{{ branch.commune }}</p>
                    <h1 class="mt-3 font-serif text-4xl text-brand-950 sm:text-5xl">{{ branch.name }}</h1>
                    <iframe
                        :src="branch.mapEmbedUrl"
                        :title="`Mapa de ${branch.name}`"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        class="mt-8 aspect-[4/3] w-full rounded-card border-0 bg-paper-100"
                    />
                </section>
                <aside class="self-start rounded-card border border-paper-200 bg-paper-50 p-7">
                    <h2 class="font-serif text-2xl">Visítanos</h2>
                    <address class="mt-5 not-italic">
                        <p>{{ branch.address }}</p>
                        <p>{{ branch.commune }}, {{ branch.region }}</p>
                    </address>
                    <h2 class="mt-7 font-serif text-xl">Horario</h2>
                    <dl class="mt-3 flex flex-col gap-3 text-sm">
                        <div v-for="hours in branch.openingHours" :key="hours.days">
                            <dt class="font-semibold">{{ hours.days }}</dt>
                            <dd class="text-paper-600">{{ hours.periods.join(' · ') }}</dd>
                        </div>
                    </dl>
                    <div class="mt-7 flex flex-col gap-3 text-sm">
                        <a v-if="branch.phoneUrl" :href="branch.phoneUrl" class="text-brand-800 underline">{{
                            branch.phone
                        }}</a
                        ><a
                            v-if="branch.email"
                            :href="`mailto:${branch.email}`"
                            class="break-all text-brand-800 underline"
                            >{{ branch.email }}</a
                        ><a
                            v-if="branch.whatsappUrl"
                            :href="branch.whatsappUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-brand-800 underline"
                            >WhatsApp <span class="sr-only">(abre en una pestaña nueva)</span></a
                        ><a
                            :href="branch.directionsUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-brand-800 underline"
                            >Cómo llegar <span class="sr-only">(abre en una pestaña nueva)</span></a
                        >
                    </div>
                </aside>
            </div>
        </Container>
    </div>
</template>
