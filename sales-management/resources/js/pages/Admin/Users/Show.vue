<template>
    <Head title="User Details" />

    <AuthenticatedLayout>
        <template #header>
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
                    <Link
                        :href="route('admin.users.edit', user.id)"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit User
                    </Link>
                    <Link
                        :href="route('admin.users.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Users
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- User Profile Card -->
                    <div class="lg:col-span-1">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex flex-col items-center">
                                    <!-- Avatar -->
                                    <div class="h-24 w-24 bg-gray-300 rounded-full flex items-center justify-center mb-4">
                                        <span class="text-2xl font-bold text-gray-700">
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </span>
                                    </div>

                                    <!-- User Name -->
                                    <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                        {{ user.name }}
                                    </h3>

                                    <!-- Role Badge -->
                                    <span :class="getRoleBadgeClass(user.role)" class="px-3 py-1 rounded-full text-sm font-medium mb-4">
                                        {{ formatRole(user.role) }}
                                    </span>

                                    <!-- Status Badge -->
                                    <div class="flex items-center mb-4">
                                        <span :class="user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                            <span :class="user.is_active ? 'bg-green-400' : 'bg-red-400'"
                                                  class="w-2 h-2 rounded-full mr-1"></span>
                                            {{ user.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h4>
                                <div class="space-y-3">
                                    <button
                                        @click="toggleUserStatus"
                                        :class="user.is_active ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-green-50 text-green-700 hover:bg-green-100'"
                                        class="w-full flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200"
                                    >
                                        <svg v-if="user.is_active" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
                                        </svg>
                                        <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ user.is_active ? 'Deactivate User' : 'Activate User' }}
                                    </button>

                                    <button
                                        @click="resetPassword"
                                        class="w-full flex items-center px-3 py-2 bg-yellow-50 text-yellow-700 hover:bg-yellow-100 rounded-md text-sm font-medium transition-colors duration-200"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                        </svg>
                                        Reset Password
                                    </button>

                                    <button
                                        @click="deleteUser"
                                        class="w-full flex items-center px-3 py-2 bg-red-50 text-red-700 hover:bg-red-100 rounded-md text-sm font-medium transition-colors duration-200"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Delete User
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Details -->
                    <div class="lg:col-span-2">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h4 class="text-lg font-medium text-gray-900 mb-6">User Information</h4>

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
                            </div>
                        </div>

                        <!-- Activity Log / Additional Information -->
                        <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h4>
                                <div class="text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No activity logs</h3>
                                    <p class="mt-1 text-sm text-gray-500">Activity logging is not yet implemented.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

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

const props = defineProps<{
    user: User;
}>();
const user = props.user;

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

const toggleUserStatus = () => {
    if (confirm(`Are you sure you want to ${user.is_active ? 'deactivate' : 'activate'} this user?`)) {
        router.patch(route('admin.users.update', user.id), {
            is_active: !user.is_active
        }, {
            preserveScroll: true,
            onSuccess: () => {
                // Handle success
            }
        });
    }
};

const resetPassword = () => {
    if (confirm('Are you sure you want to reset this user\'s password? They will receive an email with reset instructions.')) {
        router.post(route('admin.users.reset-password', user.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                // Handle success
            }
        });
    }
};

const deleteUser = () => {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        router.delete(route('admin.users.destroy', user.id), {
            onSuccess: () => {
                // Redirect will be handled by the controller
            }
        });
    }
};
</script>
