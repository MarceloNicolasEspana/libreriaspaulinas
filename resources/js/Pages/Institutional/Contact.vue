<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import Breadcrumbs from '@/Components/Breadcrumbs.vue';
import Container from '@/Components/Container.vue';

const props = defineProps({ seo: { type: Object, required: true }, formAction: { type: String, required: true } });
const page = usePage();
const institution = page.props.institution;
const form = useForm({ name: '', email: '', phone: '', subject: '', message: '', website: '' });
const fieldClass = 'mt-2 w-full rounded-control border border-paper-300 bg-white px-3 py-2.5 focus:border-brand-600';

function submit() {
    form.post(props.formAction, { preserveScroll: true, onSuccess: () => form.reset() });
}
</script>

<template>
    <div>
        <Container class="py-14 sm:py-20">
            <Breadcrumbs :items="seo.breadcrumbs" />
            <div class="grid gap-12 lg:grid-cols-[minmax(0,1fr)_22rem]">
                <section>
                    <p class="text-sm font-semibold tracking-widest text-accent-700 uppercase">Estamos para ayudarte</p>
                    <h1 class="mt-3 font-serif text-4xl text-brand-950 sm:text-5xl">Contacto</h1>
                    <p class="mt-4 max-w-2xl text-lg text-paper-600">Escríbenos y cuéntanos cómo podemos orientarte.</p>

                    <form class="mt-8 grid gap-5 sm:grid-cols-2" :aria-busy="form.processing" @submit.prevent="submit">
                        <div>
                            <label for="contact-name" class="text-sm font-semibold">Nombre</label
                            ><input
                                id="contact-name"
                                v-model="form.name"
                                required
                                autocomplete="name"
                                :class="fieldClass"
                                :aria-invalid="!!form.errors.name"
                            />
                            <p v-if="form.errors.name" role="alert" class="mt-2 text-sm text-accent-700">
                                {{ form.errors.name }}
                            </p>
                        </div>
                        <div>
                            <label for="contact-email" class="text-sm font-semibold">Correo electrónico</label
                            ><input
                                id="contact-email"
                                v-model="form.email"
                                type="email"
                                required
                                autocomplete="email"
                                :class="fieldClass"
                                :aria-invalid="!!form.errors.email"
                            />
                            <p v-if="form.errors.email" role="alert" class="mt-2 text-sm text-accent-700">
                                {{ form.errors.email }}
                            </p>
                        </div>
                        <div>
                            <label for="contact-phone" class="text-sm font-semibold"
                                >Teléfono <span class="font-normal text-paper-600">(opcional)</span></label
                            ><input
                                id="contact-phone"
                                v-model="form.phone"
                                type="tel"
                                autocomplete="tel"
                                :class="fieldClass"
                                :aria-invalid="!!form.errors.phone"
                            />
                            <p v-if="form.errors.phone" role="alert" class="mt-2 text-sm text-accent-700">
                                {{ form.errors.phone }}
                            </p>
                        </div>
                        <div>
                            <label for="contact-subject" class="text-sm font-semibold">Asunto</label
                            ><input
                                id="contact-subject"
                                v-model="form.subject"
                                required
                                :class="fieldClass"
                                :aria-invalid="!!form.errors.subject"
                            />
                            <p v-if="form.errors.subject" role="alert" class="mt-2 text-sm text-accent-700">
                                {{ form.errors.subject }}
                            </p>
                        </div>
                        <div class="hidden" aria-hidden="true">
                            <label for="contact-website">Sitio web</label
                            ><input id="contact-website" v-model="form.website" tabindex="-1" autocomplete="off" />
                        </div>
                        <div class="sm:col-span-2">
                            <label for="contact-message" class="text-sm font-semibold">Mensaje</label
                            ><textarea
                                id="contact-message"
                                v-model="form.message"
                                required
                                rows="7"
                                :class="fieldClass"
                                :aria-invalid="!!form.errors.message"
                            />
                            <p v-if="form.errors.message" role="alert" class="mt-2 text-sm text-accent-700">
                                {{ form.errors.message }}
                            </p>
                        </div>
                        <button
                            :disabled="form.processing"
                            class="rounded-control bg-accent-600 px-6 py-3 font-semibold text-white hover:bg-accent-700 disabled:cursor-not-allowed disabled:opacity-50 sm:col-span-2 sm:justify-self-start"
                        >
                            {{ form.processing ? 'Enviando…' : 'Enviar mensaje' }}
                        </button>
                    </form>
                </section>

                <aside class="self-start rounded-card bg-brand-950 p-7 text-white">
                    <h2 class="font-serif text-2xl text-white">Datos de contacto</h2>
                    <address class="mt-5 flex flex-col gap-4 text-sm text-brand-100 not-italic">
                        <p>
                            {{ institution.address.street }}, {{ institution.address.commune }},
                            {{ institution.address.city }}
                        </p>
                        <a :href="`tel:${institution.contact.phone.replace(/\s/g, '')}`" class="text-white underline">{{
                            institution.contact.phone
                        }}</a>
                        <a :href="`mailto:${institution.contact.sales_email}`" class="break-all text-white underline">{{
                            institution.contact.sales_email
                        }}</a>
                    </address>
                </aside>
            </div>
        </Container>
    </div>
</template>
