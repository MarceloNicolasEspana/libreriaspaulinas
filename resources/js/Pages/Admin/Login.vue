<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
defineOptions({ layout: false });
const props = defineProps({ submitHref: { type: String, required: true } });
const form = useForm({ email: '', password: '', remember: false });
function submit() {
    form.post(props.submitHref, { onFinish: () => form.reset('password') });
}
</script>
<template>
    <div class="grid min-h-screen place-items-center bg-brand-950 px-5 py-12">
        <Head title="Acceso administrativo">
            <meta name="robots" content="noindex,nofollow" />
        </Head>
        <section class="w-full max-w-md rounded-card bg-white p-8 shadow-xl">
            <p class="text-xs font-semibold tracking-widest text-accent-700 uppercase">Paulinas · Administración</p>
            <h1 class="mt-3 font-serif text-3xl">Bienvenido</h1>
            <p class="mt-3 text-sm text-paper-600">Ingresa con tu cuenta administradora.</p>
            <form class="mt-7 flex flex-col gap-5" @submit.prevent="submit">
                <div>
                    <label for="email" class="text-sm font-semibold">Correo electrónico</label
                    ><input
                        id="email"
                        v-model="form.email"
                        type="email"
                        autocomplete="username"
                        required
                        autofocus
                        class="mt-2 w-full rounded-control border border-paper-300 px-3 py-2"
                        :aria-invalid="!!form.errors.email"
                    />
                    <p v-if="form.errors.email" role="alert" class="mt-2 text-sm text-accent-700">
                        {{ form.errors.email }}
                    </p>
                </div>
                <div>
                    <label for="password" class="text-sm font-semibold">Contraseña</label
                    ><input
                        id="password"
                        v-model="form.password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="mt-2 w-full rounded-control border border-paper-300 px-3 py-2"
                        :aria-invalid="!!form.errors.password"
                    />
                    <p v-if="form.errors.password" role="alert" class="mt-2 text-sm text-accent-700">
                        {{ form.errors.password }}
                    </p>
                </div>
                <label class="flex items-center gap-2 text-sm"
                    ><input v-model="form.remember" type="checkbox" />Recordarme</label
                >
                <button
                    :disabled="form.processing"
                    class="rounded-control bg-brand-800 px-5 py-3 font-semibold text-white hover:bg-brand-950 disabled:opacity-50"
                >
                    {{ form.processing ? 'Ingresando…' : 'Ingresar' }}
                </button>
            </form>
            <Link href="/" class="mt-6 inline-block text-sm text-paper-600 underline">Volver al sitio</Link>
        </section>
    </div>
</template>
