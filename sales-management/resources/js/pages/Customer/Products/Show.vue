<template>
    <Head :title="product.name" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ product.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <img
                            :src="product.image || '/images/placeholder.png'"
                            :alt="product.name"
                            class="w-full h-64 object-cover rounded-md mb-4"
                        />
                        <h3 class="text-lg font-semibold mb-2">{{ product.name }}</h3>
                        <p class="text-gray-600 mb-2">{{ product.description }}</p>
                        <p class="text-gray-600">Category: {{ product.category?.name }}</p>
                        <p class="text-gray-600">Brand: {{ product.brand?.name }}</p>
                        <p class="text-indigo-600 font-bold mt-2">${{ product.price }}</p>
                        <button class="mt-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    product: {
        id: number;
        name: string;
        description: string;
        price: number;
        image?: string;
        category?: { name: string };
        brand?: { name: string };
    };
}>();
</script>
