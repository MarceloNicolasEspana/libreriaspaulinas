<script setup>
import { useInstitution } from '@/Composables/useInstitution';

defineProps({
    /** "full" incluye el wordmark; "mark" muestra solo el símbolo. */
    variant: {
        type: String,
        default: 'full',
        validator: (v) => ['full', 'mark'].includes(v),
    },
});

const { institution } = useInstitution();
</script>

<template>
    <span class="inline-flex shrink-0 items-center">
        <img
            :src="variant === 'full' ? '/images/logo/logo_paulinas_texto.png' : '/images/logo/logo_paulinas.png'"
            :alt="variant === 'full' ? institution.legalName : institution.shortName"
            :class="variant === 'full' ? 'h-10 w-auto sm:h-12 lg:h-14' : 'size-9 object-contain'"
        />
    </span>
</template>
