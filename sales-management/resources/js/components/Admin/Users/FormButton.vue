<template>
    <Button
        :type="type"
        :variant="variant"
        :disabled="disabled"
        :as="href ? 'a' : 'button'"
        :href="href"
        class="font-bold"
    >
        <slot />
    </Button>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';

defineProps<{
    type?: 'button' | 'submit' | 'reset';
    variant?: 'default' | 'secondary' | 'destructive' | 'outline' | 'ghost' | 'link';
    disabled?: boolean;
    href?: string;
}>();
</script>
