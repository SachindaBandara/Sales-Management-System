<template>
    <div class="border rounded-lg p-4 hover:shadow-lg transition-shadow">
        <img
            :src="product.image || '/images/placeholder.png'"
            :alt="product.name"
            class="w-full h-48 object-cover rounded-md mb-4"
        />
        <h4 class="font-semibold text-gray-800">{{ product.name }}</h4>
        <p class="text-gray-600 text-sm">{{ product.category?.name }}</p>
        <p class="text-gray-600 text-sm">{{ product.brand?.name }}</p>
        <p class="text-indigo-600 font-bold mt-2">LKR {{ product.price }}</p>
        <div class="mt-2 flex space-x-2">
            <Link
                :href="route('customer.products.show', product.id)"
                class="inline-block bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded"
            >
                View Details
            </Link>
            <button
                v-if="product.is_in_stock"
                @click="$emit('add-to-cart', product.id)"
                class="inline-block bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                :disabled="addingToCart"
            >
                {{ addingToCart ? 'Adding...' : 'Add to Cart' }}
            </button>
            <span v-else class="text-red-600 font-semibold">Out of Stock</span>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface Category {
    id: number;
    name: string;
    slug: string;
    products_count: number;
}

interface Brand {
    id: number;
    name: string;
    slug: string;
    products_count: number;
}

interface Product {
    id: number;
    name: string;
    price: number;
    image?: string;
    category?: Category;
    brand?: Brand;
    is_in_stock: boolean;
}

defineProps<{
    product: Product;
    addingToCart: boolean;
}>();

defineEmits(['add-to-cart']);
</script>
