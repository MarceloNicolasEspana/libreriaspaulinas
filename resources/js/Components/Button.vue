<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (v) => ['primary', 'secondary', 'outline', 'ghost', 'link'].includes(v),
    },
    size: {
        type: String,
        default: 'md',
        validator: (v) => ['sm', 'md', 'lg'].includes(v),
    },
    /** Si se entrega, el botón se renderiza como enlace Inertia. */
    href: { type: String, default: null },
    /** Fuerza un <a> nativo, para enlaces externos o mailto:/tel:. */
    external: { type: Boolean, default: false },
    type: { type: String, default: 'button' },
    disabled: { type: Boolean, default: false },
    block: { type: Boolean, default: false },
});

const variants = {
    primary: 'bg-accent-600 text-white shadow-subtle hover:bg-accent-700 active:bg-accent-800',
    secondary: 'bg-brand-700 text-white shadow-subtle hover:bg-brand-800 active:bg-brand-900',
    outline:
        'border border-brand-200 bg-white text-brand-800 hover:border-brand-300 hover:bg-brand-50 active:bg-brand-100',
    ghost: 'text-brand-800 hover:bg-brand-50 active:bg-brand-100',
    link: 'text-accent-700 underline decoration-accent-300 underline-offset-4 hover:decoration-accent-600',
};

const sizes = {
    sm: 'h-9 gap-1.5 px-3.5 text-sm',
    md: 'h-11 gap-2 px-5 text-sm',
    lg: 'h-13 gap-2.5 px-7 text-base',
};

// El variant "link" no debe heredar la altura ni el fondo de un botón.
const classes = computed(() => [
    'inline-flex items-center justify-center rounded-control font-medium transition-colors duration-150',
    'disabled:pointer-events-none disabled:opacity-50 aria-disabled:pointer-events-none aria-disabled:opacity-50',
    props.variant === 'link' ? 'p-0 text-sm' : sizes[props.size],
    variants[props.variant],
    props.block ? 'w-full' : '',
]);

const component = computed(() => {
    if (!props.href) {
        return 'button';
    }

    return props.external ? 'a' : Link;
});

// Un enlace deshabilitado no puede usar el atributo "disabled": se marca con
// aria-disabled y se le quitan los eventos vía CSS.
const attrs = computed(() =>
    props.href
        ? { href: props.href, 'aria-disabled': props.disabled || undefined }
        : { type: props.type, disabled: props.disabled },
);
</script>

<template>
    <component :is="component" v-bind="attrs" :class="classes">
        <slot name="icon" />
        <slot />
    </component>
</template>
