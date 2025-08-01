<template>
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Admin Dashboard
        </h2>
        <div class="flex items-center space-x-2 text-sm text-gray-500">
            <CalendarIcon class="h-4 w-4" />
            <span>{{ currentDate }}</span>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { CalendarIcon } from 'lucide-vue-next';

const currentDate = computed(() => {
    return new Date().toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
});
</script>
