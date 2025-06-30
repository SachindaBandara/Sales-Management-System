<template>
    <DropdownMenu v-bind="$attrs">
        <DropdownMenuTrigger as-child>
            <slot name="trigger" />
        </DropdownMenuTrigger>
        <DropdownMenuContent :align="computedAlign" :style="{ width: width ? `${width}px` : 'auto' }">
            <slot name="content" />
        </DropdownMenuContent>
    </DropdownMenu>
</template>

<script setup lang="ts">
import { DropdownMenu, DropdownMenuTrigger, DropdownMenuContent } from '@/components/ui/dropdown-menu';
import { defineProps, computed } from 'vue';

const props = defineProps<{
    align?: 'left' | 'right' | 'center';
    width?: string | number;
}>();

const computedAlign = computed(() => {
    if (props.align === 'left') return 'start';
    if (props.align === 'right') return 'end';
    return props.align;
});
</script>
