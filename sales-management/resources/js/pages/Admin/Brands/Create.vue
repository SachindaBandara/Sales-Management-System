<template>
    <Head title="Create Brand" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Create New Brand
                </h2>
                <Link :href="route('admin.brands.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Brands
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                />
                                <div v-if="errors?.name" class="text-red-600 text-sm mt-1">{{ errors?.name }}</div>
                            </div>

                            <div class="mb-4">
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    rows="4"
                                ></textarea>
                                <div v-if="errors?.description" class="text-red-600 text-sm mt-1">{{ errors?.description }}</div>
                            </div>

                            <div class="mb-4">
                                <label for="logo" class="block text-sm font-medium text-gray-700">Logo</label>
                                <input
                                    id="logo"
                                    type="file"
                                    accept="image/*"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    @change="form.logo = ($event.target as HTMLInputElement).files?.[0] ?? null"
                                />
                                <div v-if="errors?.logo" class="text-red-600 text-sm mt-1">{{ errors?.logo }}</div>
                            </div>

                            <div class="mb-6">
                                <label class="flex items-center">
                                    <input
                                        v-model="form.is_active"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-600">Active Brand</span>
                                </label>
                            </div>

                            <div class="flex justify-end space-x-4">
                                <Link :href="route('admin.brands.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                                >
                                    Create Brand
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from '../../../layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    errors: Object,
});

const form = useForm({
    name: '',
    description: '',
    logo: null as File | null,
    is_active: true,
});

const submit = () => {
    form.post(route('admin.brands.store'), {
        forceFormData: true, // Required for file uploads
    });
};
</script>
