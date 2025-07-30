<template>
    <Card>
        <CardContent class="flex flex-col items-center p-6">
            <div class="h-24 w-24 bg-gray-300 rounded-full flex items-center justify-center mb-4">
                <span class="text-2xl font-bold text-gray-700">
                    {{ user.name.charAt(0).toUpperCase() }}
                </span>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">
                {{ user.name }}
            </h3>
            <Badge :class="getRoleBadgeClass(user.role)">
                {{ formatRole(user.role) }}
            </Badge>
            <div class="flex items-center mt-4">
                <Badge
                    :variant="user.is_active ? 'default' : 'destructive'"
                    :class="user.is_active ? 'bg-green-100 text-green-800' : ''"
                >
                    <span :class="user.is_active ? 'bg-green-400' : 'bg-red-400'"
                          class="w-2 h-2 rounded-full mr-1"></span>
                    {{ user.is_active ? 'Active' : 'Inactive' }}
                </Badge>
            </div>
        </CardContent>
    </Card>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    is_active: boolean;
}

defineProps<{
    user: User;
}>();

const formatRole = (role: string): string => {
    return role.charAt(0).toUpperCase() + role.slice(1);
};

const getRoleBadgeClass = (role: string): string => {
    const classes = {
        admin: 'bg-purple-100 text-purple-800',
        customer: 'bg-blue-100 text-blue-800',
        manager: 'bg-green-100 text-green-800',
    };
    return classes[role as keyof typeof classes] || 'bg-gray-100 text-gray-800';
};
</script>
