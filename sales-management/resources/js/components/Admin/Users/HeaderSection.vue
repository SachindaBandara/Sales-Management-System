<template>
    <div class="flex items-center justify-between">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                User Details
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                View and manage user information
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <Button as-child>
                <Link :href="route('admin.users.edit', user.id)">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit User
                </Link>
            </Button>
            <Button variant="secondary" as-child>
                <Link :href="route('admin.users.index')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Users
                </Link>
            </Button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

interface User {
    id: number;
}

defineProps<{
    user: User;
}>();
</script>
