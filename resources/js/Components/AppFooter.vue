<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLogo from '@/Components/AppLogo.vue';
import Container from '@/Components/Container.vue';
import FacebookIcon from '@/Components/Icons/FacebookIcon.vue';
import MailIcon from '@/Components/Icons/MailIcon.vue';
import MapPinIcon from '@/Components/Icons/MapPinIcon.vue';
import PhoneIcon from '@/Components/Icons/PhoneIcon.vue';
import WhatsappIcon from '@/Components/Icons/WhatsappIcon.vue';
import { useInstitution } from '@/Composables/useInstitution';
import { useNavigation } from '@/Composables/useNavigation';

const { institution, fullAddress } = useInstitution();
const { footerColumns } = useNavigation();

const currentYear = new Date().getFullYear();

const socialIcons = { facebook: FacebookIcon };

// Solo se listan las redes con URL confirmada en config/paulinas.php.
const socialLinks = computed(() =>
    Object.entries(institution.value.social).map(([network, url]) => ({
        network,
        url,
        icon: socialIcons[network],
    })),
);

const whatsappUrl = computed(() => `https://wa.me/${institution.value.contact.whatsapp.replace(/\D/g, '')}`);
</script>

<template>
    <footer class="mt-20 border-t border-brand-100 bg-brand-50/50">
        <Container class="py-14">
            <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-12 lg:gap-8">
                <!-- Bloque institucional -->
                <div class="lg:col-span-3">
                    <AppLogo />
                    <p class="mt-4 max-w-xs font-serif text-lg leading-snug text-brand-900">
                        {{ institution.tagline }}
                    </p>
                    <p class="mt-3 text-sm text-paper-600">{{ institution.legalName }}</p>
                </div>

                <!-- Columnas de enlaces -->
                <div v-for="column in footerColumns" :key="column.heading" class="lg:col-span-2">
                    <h2 class="font-serif text-base font-semibold text-brand-900">{{ column.heading }}</h2>
                    <ul class="mt-4 space-y-2.5">
                        <li v-for="link in column.links" :key="link.href">
                            <Link
                                :href="link.href"
                                class="text-sm text-paper-700 transition-colors hover:text-accent-700"
                            >
                                {{ link.label }}
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Contacto -->
                <div class="lg:col-span-3">
                    <h2 class="font-serif text-base font-semibold text-brand-900">Contacto</h2>
                    <ul class="mt-4 space-y-3 text-sm text-paper-700">
                        <li class="flex gap-2.5">
                            <MapPinIcon class="mt-0.5 size-4.5 shrink-0 text-brand-600" />
                            <span>{{ fullAddress }}</span>
                        </li>
                        <li class="flex gap-2.5">
                            <PhoneIcon class="mt-0.5 size-4.5 shrink-0 text-brand-600" />
                            <a
                                :href="`tel:${institution.contact.phone.replace(/\s/g, '')}`"
                                class="transition-colors hover:text-accent-700"
                            >
                                {{ institution.contact.phone }}
                            </a>
                        </li>
                        <li class="flex gap-2.5">
                            <MailIcon class="mt-0.5 size-4.5 shrink-0 text-brand-600" />
                            <span class="flex flex-col gap-1">
                                <a
                                    :href="`mailto:${institution.contact.sales_email}`"
                                    class="break-words transition-colors hover:text-accent-700"
                                >
                                    {{ institution.contact.sales_email }}
                                </a>
                                <a
                                    :href="`mailto:${institution.contact.distribution_email}`"
                                    class="break-words transition-colors hover:text-accent-700"
                                >
                                    {{ institution.contact.distribution_email }}
                                </a>
                            </span>
                        </li>
                    </ul>

                    <div class="mt-5 flex items-center gap-2">
                        <a
                            :href="whatsappUrl"
                            target="_blank"
                            rel="noopener"
                            aria-label="Escribir por WhatsApp"
                            class="inline-flex size-10 items-center justify-center rounded-full border border-brand-200 bg-white text-brand-700 transition-colors hover:border-accent-300 hover:text-accent-700"
                        >
                            <WhatsappIcon class="size-5" />
                        </a>
                        <a
                            v-for="social in socialLinks"
                            :key="social.network"
                            :href="social.url"
                            target="_blank"
                            rel="noopener"
                            :aria-label="`Paulinas en ${social.network}`"
                            class="inline-flex size-10 items-center justify-center rounded-full border border-brand-200 bg-white text-brand-700 capitalize transition-colors hover:border-accent-300 hover:text-accent-700"
                        >
                            <component :is="social.icon" v-if="social.icon" class="size-5" />
                            <span v-else class="text-xs">{{ social.network.slice(0, 2) }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </Container>

        <div class="border-t border-brand-100">
            <Container
                class="flex flex-col gap-2 py-5 text-xs text-paper-600 sm:flex-row sm:items-center sm:justify-between"
            >
                <p>© {{ currentYear }} {{ institution.legalName }}</p>
                <p>{{ fullAddress }}</p>
            </Container>
        </div>
    </footer>
</template>
