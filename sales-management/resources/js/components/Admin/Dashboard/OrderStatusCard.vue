<template>
    <Card class="p-4">
        <div class="text-center">
            <template v-if="isLink">
                <Link :href="route('admin.orders.index')" class="block hover:bg-gray-50 transition-colors">
                    <p class="text-sm text-gray-600">{{ title }}</p>
                    <p class="text-xl font-bold text-blue-600">{{ value }}</p>
                    <ExternalLinkIcon class="h-4 w-4 mx-auto mt-1 text-gray-400" />
                </Link>
            </template>
            <template v-else>
                <p class="text-sm text-gray-600">{{ title }}</p>
                <p :class="['text-xl font-bold', textColor]">{{ value }}</p>
            </template>
        </div>
    </Card>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Card } from '@/components/ui/card';
import { ExternalLinkIcon } from 'lucide-vue-next';

interface Props {
    title: string;
    value: string | number;
    textColor?: string;
    isLink?: boolean;
}

defineProps<Props>();
</script>
