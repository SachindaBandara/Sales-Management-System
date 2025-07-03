<script setup lang="ts">
import AuthenticatedLayout from '../../../layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

interface Category {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    image: string | null;
    parent: Category | null;
    children: Category[];
    is_active: boolean;
    created_at: string;
}

defineProps<{
    category: Category;
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
    <Head title="View Category" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                View Category
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
                                        v-if="category.image"
                                        :src="category.image"
                                        class="h-16 w-16 rounded-full object-cover"
                                        alt="Category image"
                                        @error="($event.target as HTMLImageElement).src = '/images/default-image.png'"
                                    />
                                    <div
                                        v-else
                                        class="h-16 w-16 rounded-full flex items-center justify-center bg-blue-100 text-blue-600 font-semibold text-lg"
                                    >
                                        {{ category.name.charAt(0).toUpperCase() }}
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">{{ category.name }}</h3>
                                    <p class="text-sm text-gray-500">{{ category.slug }}</p>
                                </div>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-700">Description</h4>
                                <p class="text-sm text-gray-500">{{ category.description || 'No description provided.' }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-700">Status</h4>
                                <span
                                    :class="category.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                >
                                    {{ category.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-700">Parent Category</h4>
                                <p class="text-sm text-gray-500">{{ category.parent ? category.parent.name : 'None' }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-700">Subcategories</h4>
                                <p v-if="category.children.length === 0" class="text-sm text-gray-500">No subcategories</p>
                                <ul v-else class="list-disc list-inside text-sm text-gray-500">
                                    <li v-for="child in category.children" :key="child.id">{{ child.name }}</li>
                                </ul>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-700">Created</h4>
                                <p class="text-sm text-gray-500">{{ formatDate(category.created_at) }}</p>
                            </div>
                            <div class="flex gap-2">
                                <Link
                                    :href="route('admin.categories.edit', category.id)"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200"
                                >
                                    Edit
                                </Link>
                                <Link
                                    :href="route('admin.categories.index')"
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