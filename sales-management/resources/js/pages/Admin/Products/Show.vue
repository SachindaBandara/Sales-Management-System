<script setup lang="ts">
import AuthenticatedLayout from '../../../layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

interface Brand {
    id: number;
    name: string;
}

interface Category {
    id: number;
    name: string;
}

interface Product {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    short_description: string | null;
    sku: string;
    price: number;
    compare_price: number | null;
    stock_quantity: number;
    track_quantity: boolean;
    weight: number | null;
    dimensions: {
        length?: number;
        width?: number;
        height?: number;
    } | null;
    status: string;
    brand: Brand | null;
    category: Category | null;
    images: string[];
    created_at: string;
}

defineProps<{
    product: Product;
}>();

const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatPrice = (price: number): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'LKR'
    }).format(price);
};

const formatDimensions = (dimensions: Product['dimensions']): string => {
    if (!dimensions || (!dimensions.length && !dimensions.width && !dimensions.height)) {
        return 'Not specified';
    }
    return `${dimensions.length || 0} x ${dimensions.width || 0} x ${dimensions.height || 0} cm`;
};
</script>

<template>
    <Head title="View Product" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                View Product
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-16 w-16">
                                    <img
                                        v-if="product.images && product.images.length > 0"
                                        :src="product.images[0]"
                                        class="h-16 w-16 rounded-full object-cover"
                                        alt="Product image"
                                        @error="($event.target as HTMLImageElement).src = '/images/default-product.png'"
                                    />
                                    <div
                                        v-else
                                        class="h-16 w-16 rounded-full flex items-center justify-center bg-blue-100 text-blue-600 font-semibold text-lg"
                                    >
                                        {{ product.name.charAt(0).toUpperCase() }}
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">{{ product.name }}</h3>
                                    <p class="text-sm text-gray-500">{{ product.slug }}</p>
                                </div>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-700">SKU</h4>
                                <p class="text-sm text-gray-500">{{ product.sku }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <h4 class="text-sm font-medium text-gray-700">Description</h4>
                                <p class="text-sm text-gray-500">{{ product.description || 'No description provided.' }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <h4 class="text-sm font-medium text-gray-700">Short Description</h4>
                                <p class="text-sm text-gray-500">{{ product.short_description || 'No short description provided.' }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-700">Price</h4>
                                <p class="text-sm text-gray-500">
                                    {{ formatPrice(product.price) }}
                                    <span v-if="product.compare_price" class="line-through text-gray-400 ml-2">
                                        {{ formatPrice(product.compare_price) }}
                                    </span>
                                </p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-700">Stock Quantity</h4>
                                <p class="text-sm text-gray-500">{{ product.track_quantity ? product.stock_quantity : 'N/A' }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-700">Track Quantity</h4>
                                <p class="text-sm text-gray-500">{{ product.track_quantity ? 'Yes' : 'No' }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-700">Weight</h4>
                                <p class="text-sm text-gray-500">{{ product.weight ? `${product.weight} kg` : 'Not specified' }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-700">Dimensions</h4>
                                <p class="text-sm text-gray-500">{{ formatDimensions(product.dimensions) }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-700">Status</h4>
                                <span
                                    :class="{
                                        'bg-green-100 text-green-800': product.status === 'active',
                                        'bg-gray-100 text-gray-800': product.status === 'draft',
                                        'bg-red-100 text-red-800': product.status === 'archived'
                                    }"
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                >
                                    {{ product.status.charAt(0).toUpperCase() + product.status.slice(1) }}
                                </span>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-700">Brand</h4>
                                <p class="text-sm text-gray-500">{{ product.brand?.name || 'No brand assigned' }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-700">Category</h4>
                                <p class="text-sm text-gray-500">{{ product.category?.name || 'No category assigned' }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <h4 class="text-sm font-medium text-gray-700">Images</h4>
                                <div class="flex flex-wrap gap-2">
                                    <img
                                        v-for="image in product.images"
                                        :key="image"
                                        :src="image"
                                        class="h-16 w-16 rounded-full object-cover"
                                        alt="Product image"
                                        @error="($event.target as HTMLImageElement).src = '/images/default-product.png'"
                                    />
                                    <p v-if="!product.images || product.images.length === 0" class="text-sm text-gray-500">
                                        No images available.
                                    </p>
                                </div>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-700">Created</h4>
                                <p class="text-sm text-gray-500">{{ formatDate(product.created_at) }}</p>
                            </div>
                            <div class="md:col-span-2 flex gap-2">
                                <Link
                                    :href="route('admin.products.edit', product.id)"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200"
                                >
                                    Edit
                                </Link>
                                <Link
                                    :href="route('admin.products.index')"
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