<script setup lang="ts">
import AuthenticatedLayout from '../../../layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

interface Brand {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    logo: string | null;
    is_active: boolean;
    created_at: string;
}

defineProps<{
    brand: Brand;
}>();

const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="View Brand" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                View Brand
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-16 w-16">
                                    <img
                                        v-if="brand.logo"
                                        :src="brand.logo"
                                        class="h-16 w-16 rounded-full object-cover"
                                        alt="Brand logo"
                                        @error="($event.target as HTMLImageElement).src = '/images/default-logo.png'"
                                    />
                                    <div
                                        v-else
                                        class="h-16 w-16 rounded-full flex items-center justify-center bg-blue-100 text-blue-600 font-semibold text-lg"
                                    >
                                        {{ brand.name.charAt(0).toUpperCase() }}
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">{{ brand.name }}</h3>
                                    <p class="text-sm text-gray-500">{{ brand.slug }}</p>
                                </div>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-700">Description</h4>
                                <p class="text-sm text-gray-500">{{ brand.description || 'No description provided.' }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-700">Status</h4>
                                <span
                                    :class="brand.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                >
                                    {{ brand.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-700">Created</h4>
                                <p class="text-sm text-gray-500">{{ formatDate(brand.created_at) }}</p>
                            </div>
                            <div class="flex gap-2">
                                <Link
                                    :href="route('admin.brands.edit', brand.id)"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200"
                                >
                                    Edit
                                </Link>
                                <Link
                                    :href="route('admin.brands.index')"
                                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200"
                                >
                                    Back
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>