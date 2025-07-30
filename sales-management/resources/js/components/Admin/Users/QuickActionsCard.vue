<template>
    <Card>
        <CardHeader>
            <CardTitle>Quick Actions</CardTitle>
        </CardHeader>
        <CardContent class="space-y-3">
            <Button
                :variant="user.is_active ? 'destructive' : 'default'"
                :class="user.is_active ? '' : 'bg-green-50 text-green-700 hover:bg-green-100'"
                class="w-full flex items-center"
                @click="$emit('toggle-status')"
            >
                <svg v-if="user.is_active" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
                </svg>
                <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ user.is_active ? 'Deactivate User' : 'Activate User' }}
            </Button>
            <Button
                variant="default"
                class="w-full flex items-center bg-yellow-50 text-yellow-700 hover:bg-yellow-100"
                @click="$emit('reset-password')"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                Reset Password
            </Button>
            <Button
                variant="destructive"
                class="w-full flex items-center"
                @click="$emit('delete-user')"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Delete User
            </Button>
        </CardContent>
    </Card>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';

interface User {
    id: number;
    is_active: boolean;
}

defineProps<{
    user: User;
}>();

defineEmits(['toggle-status', 'reset-password', 'delete-user']);
</script>
