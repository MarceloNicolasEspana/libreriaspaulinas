<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AppLogo from '@/Components/AppLogo.vue';
import Container from '@/Components/Container.vue';
import HeaderAction from '@/Components/HeaderAction.vue';
import MobileMenu from '@/Components/MobileMenu.vue';
import NavLink from '@/Components/NavLink.vue';
import SearchBar from '@/Components/SearchBar.vue';
import CartIcon from '@/Components/Icons/CartIcon.vue';
import HeartIcon from '@/Components/Icons/HeartIcon.vue';
import MenuIcon from '@/Components/Icons/MenuIcon.vue';
import SearchIcon from '@/Components/Icons/SearchIcon.vue';
import UserIcon from '@/Components/Icons/UserIcon.vue';
import { useInstitution } from '@/Composables/useInstitution';
import { useNavigation } from '@/Composables/useNavigation';

const { institution } = useInstitution();
const { primary } = useNavigation();

const menuOpen = ref(false);
// En móvil el buscador se despliega bajo la barra para no competir con el logo.
const searchOpen = ref(false);

/*
 * Término de la página actual, si la hay. Estando en un resultado de búsqueda,
 * el campo del header debe mostrar lo que se buscó en vez de aparecer vacío:
 * de otro modo parecería que la búsqueda no se aplicó.
 */
const page = usePage();
const searchTerm = computed(() => page.props.filters?.q ?? '');
</script>

<template>
    <header class="sticky top-0 z-40 border-b border-brand-100 bg-white/95 backdrop-blur-sm">
        <!-- Franja institucional: solo desde tablet, para no robar altura en móvil. -->
        <div class="hidden bg-brand-800 text-white md:block">
            <Container class="flex h-9 items-center justify-between text-xs">
                <p class="font-serif tracking-wide text-brand-100">{{ institution.tagline }}</p>
                <a
                    :href="`tel:${institution.contact.phone.replace(/\s/g, '')}`"
                    class="font-medium text-white transition-colors hover:text-gold-200"
                >
                    {{ institution.contact.phone }}
                </a>
            </Container>
        </div>

        <Container>
            <div class="flex h-20 items-center gap-4 lg:gap-8">
                <Link href="/" class="shrink-0" aria-label="Ir al inicio">
                    <AppLogo />
                </Link>

                <!-- El buscador es el elemento dominante en escritorio. -->
                <div class="hidden min-w-0 flex-1 lg:block">
                    <SearchBar :initial="searchTerm" />
                </div>

                <!--
                    La visibilidad responsive va en el <li> y no en HeaderAction:
                    su raíz ya declara "inline-flex", y una clase "hidden" en el
                    mismo elemento no ganaría por tener igual especificidad.
                -->
                <ul class="ml-auto flex items-center gap-0.5 lg:ml-0">
                    <li class="lg:hidden">
                        <HeaderAction label="Buscar" :aria-expanded="searchOpen" @click="searchOpen = !searchOpen">
                            <SearchIcon class="size-5.5" />
                        </HeaderAction>
                    </li>

                    <li class="hidden sm:block">
                        <HeaderAction label="Mi cuenta">
                            <UserIcon class="size-5.5" />
                        </HeaderAction>
                    </li>

                    <li class="hidden sm:block">
                        <HeaderAction label="Favoritos">
                            <HeartIcon class="size-5.5" />
                        </HeaderAction>
                    </li>

                    <li>
                        <HeaderAction label="Carrito de compras">
                            <CartIcon class="size-5.5" />
                        </HeaderAction>
                    </li>

                    <li class="lg:hidden">
                        <HeaderAction label="Abrir menú" @click="menuOpen = true">
                            <MenuIcon class="size-6" />
                        </HeaderAction>
                    </li>
                </ul>
            </div>

            <!-- Buscador desplegable en móvil y tablet. -->
            <div v-if="searchOpen" class="pb-4 lg:hidden">
                <SearchBar size="compact" autofocus :initial="searchTerm" @submitted="searchOpen = false" />
            </div>
        </Container>

        <!-- Navegación de categorías, solo escritorio. -->
        <nav class="hidden border-t border-brand-100 lg:block" aria-label="Categorías">
            <Container>
                <ul class="flex items-center gap-1">
                    <li v-for="item in primary" :key="item.href">
                        <NavLink :href="item.href">{{ item.label }}</NavLink>
                    </li>
                </ul>
            </Container>
        </nav>

        <MobileMenu :open="menuOpen" @close="menuOpen = false" />
    </header>
</template>
