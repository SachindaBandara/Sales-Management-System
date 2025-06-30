<template>
    <DropdownMenuItem
        v-if="as === 'button' && method === 'post'"
        as-child
    >
        <Link
            :href="href"
            :method="method"
            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
        >
            <slot />
        </Link>
    </DropdownMenuItem>
    <DropdownMenuItem
        v-else
        as-child
    >
        <Link
            :href="href"
            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
        >
            <slot />
        </Link>
    </DropdownMenuItem>
</template>

<script setup lang="ts">
import { DropdownMenuItem } from '@/components/ui/dropdown-menu';
import { Link } from '@inertiajs/vue3';
import { defineProps } from 'vue';

defineProps<{
    href: string;
    method?: string;
    as?: string;
}>();
</script>
