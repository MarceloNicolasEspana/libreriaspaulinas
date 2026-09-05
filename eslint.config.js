import js from '@eslint/js';
import pluginVue from 'eslint-plugin-vue';
import configPrettier from 'eslint-config-prettier';
import globals from 'globals';

export default [
    {
        ignores: ['public/**', 'vendor/**', 'node_modules/**', 'storage/**', 'bootstrap/ssr/**'],
    },
    js.configs.recommended,
    ...pluginVue.configs['flat/recommended'],
    {
        files: ['**/*.{js,vue}'],
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            globals: {
                ...globals.browser,
                ...globals.node,
            },
        },
        rules: {
            // Los componentes de página Inertia usan nombres de una palabra
            // (Home.vue, Contacto.vue), lo que es correcto en este contexto.
            'vue/multi-word-component-names': 'off',
        },
    },
    configPrettier,
];
