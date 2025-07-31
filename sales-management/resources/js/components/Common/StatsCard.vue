<template>
    <Card class="overflow-hidden">
        <CardContent class="p-6">
            <Link v-if="isLink && href" :href="href" :class="color">
                <div class="text-lg font-semibold">{{ title }}</div>
                <div class="text-gray-600">{{ description }}</div>
            </Link>
            <div v-else>
                <div class="text-3xl font-bold" :class="color">{{ value }}</div>
                <div class="text-gray-600">{{ title }}</div>
            </div>
        </CardContent>
    </Card>
</template>

<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card';
import { Link } from '@inertiajs/vue3';

defineProps<{
    value?: number;
    title: string;
    description?: string;
    isLink?: boolean;
    href?: string;
    color: string;
}>();
</script>
