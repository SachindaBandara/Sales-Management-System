<template>
    <Link
        v-if="as === 'button' && method === 'post'"
        :href="href"
        :method="method"
        :class="[
            'w-full flex items-start pl-3 pr-4 py-2 border-l-4 text-base transition duration-150 ease-in-out',
            active
                ? 'border-indigo-400 text-indigo-700 bg-indigo-50'
                : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300'
        ]"
    >
        <slot />
    </Link>
    <Link
        v-else
        :href="href"
        :class="[
            'w-full flex items-start pl-3 pr-4 py-2 border-l-4 text-base transition duration-150 ease-in-out',
            active
                ? 'border-indigo-400 text-indigo-700 bg-indigo-50'
                : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300'
        ]"
    >
        <slot />
    </Link>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { defineProps } from 'vue';

defineProps<{
    href: string;
    active: boolean;
    method?: string;
    as?: string;
}>();
</script>
