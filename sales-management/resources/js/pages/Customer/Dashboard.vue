<template>
    <Head title="Customer Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Welcome back, {{ user.name }}!
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-4">Customer Dashboard</h3>
                        <p class="mb-4">You're logged in as a customer! This is your personal dashboard.</p>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="border rounded-lg p-4">
                                <h4 class="font-semibold text-gray-800 mb-2">My Orders</h4>
                                <p class="text-gray-600">View your order history and track current orders.</p>
                                <button class="mt-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    View Orders
                                </button>
                            </div>

                            <div class="border rounded-lg p-4">
                                <h4 class="font-semibold text-gray-800 mb-2">My Profile</h4>
                                <p class="text-gray-600">Update your personal information and preferences.</p>
                                <Link :href="route('profile.edit')" class="mt-2 inline-block bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    Edit Profile
                                </Link>
                            </div>

                            <div class="border rounded-lg p-4">
                                <h4 class="font-semibold text-gray-800 mb-2">Support</h4>
                                <p class="text-gray-600">Need help? Contact our customer support team.</p>
                                <button class="mt-2 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    Contact Support
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

interface User {
    name: string;
}

defineProps<{
    user: User;
}>();
</script>
