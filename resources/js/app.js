import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Librerías Paulinas';

createInertiaApp({
    title: (title) => (title ? `${title} · ${appName}` : appName),

    resolve: async (name) => {
        const page = await resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue'));

        // Las páginas administrativas declaran su propio layout; el resto del
        // sitio usa el layout público por defecto.
        page.default.layout ??= PublicLayout;

        return page;
    },

    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },

    progress: {
        color: '#1d4ed8',
    },
});
