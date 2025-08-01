<template>
    <Card class="overflow-hidden">
        <CardContent class="p-6">
            <Link v-if="isLink && href" :href="href" class="block hover:bg-gray-50 transition-colors">
                <div class="text-lg font-semibold" :class="color">{{ title }}</div>
                <div class="text-sm text-gray-600">{{ description }}</div>
            </Link>
            <div v-else class="flex items-center justify-between">
                <div>
                    <div class="text-2xl font-bold" :class="color">{{ formattedValue }}</div>
                    <div class="text-sm font-medium text-gray-600">{{ title }}</div>
                </div>
                <div v-if="icon" :class="['p-3 rounded-full', backgroundColor]">
                    <component :is="icon" :class="['h-6 w-6', iconColor]" />
                </div>
            </div>
        </CardContent>
    </Card>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { Component } from 'vue';
import { Card, CardContent } from '@/components/ui/card';
import { Link } from '@inertiajs/vue3';

interface Props {
    value?: number;
    title: string;
    description?: string;
    isLink?: boolean;
    href?: string;
    color: string;
    icon?: Component;
    backgroundColor?: string;
    iconColor?: string;
    isCurrency?: boolean;
}

const props = defineProps<Props>();

const formattedValue = computed(() => {
    if (props.isCurrency && props.value !== undefined) {
        return `$${new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(props.value)}`;
    }
    return props.value !== undefined ? props.value.toLocaleString() : '';
});
</script>
