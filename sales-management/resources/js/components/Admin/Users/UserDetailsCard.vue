<template>
    <Card>
        <CardHeader>
            <CardTitle>User Information</CardTitle>
        </CardHeader>
        <CardContent>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <dt class="text-sm font-medium text-gray-500 mb-1">Full Name</dt>
                    <dd class="text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">
                        {{ user.name }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 mb-1">Email Address</dt>
                    <dd class="text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">
                        <a :href="'mailto:' + user.email" class="text-indigo-600 hover:text-indigo-900">
                            {{ user.email }}
                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 mb-1">Role</dt>
                    <dd class="text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">
                        {{ formatRole(user.role) }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 mb-1">Account Status</dt>
                    <dd class="text-sm bg-gray-50 px-3 py-2 rounded-md">
                        <span :class="user.is_active ? 'text-green-800' : 'text-red-800'"
                              class="font-medium">
                            {{ user.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 mb-1">Last Login</dt>
                    <dd class="text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">
                        {{ user.last_login_at ? formatDate(user.last_login_at) : 'Never logged in' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 mb-1">Member Since</dt>
                    <dd class="text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">
                        {{ formatDate(user.created_at) }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 mb-1">Last Updated</dt>
                    <dd class="text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md">
                        {{ formatDate(user.updated_at) }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 mb-1">User ID</dt>
                    <dd class="text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded-md font-mono">
                        #{{ user.id }}
                    </dd>
                </div>
            </dl>
        </CardContent>
    </Card>
</template>

<script setup lang="ts">
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    is_active: boolean;
    last_login_at: string | null;
    created_at: string;
    updated_at: string;
}

defineProps<{
    user: User;
}>();

const formatRole = (role: string): string => {
    return role.charAt(0).toUpperCase() + role.slice(1);
};

const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>
