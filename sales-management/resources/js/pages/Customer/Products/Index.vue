<template>
    <Head title="Shop" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Welcome to Our Shop, {{user.name}}!
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters Section -->
                <div class="bg-white shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Filters</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Search -->
                            <div>
                                <input
                                    v-model="localFilters.search"
                                    type="text"
                                    placeholder="Search products..."
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    @input="applyFilters"
                                />
                            </div>

                            <!-- Category Filter -->
                            <div>
                                <select
                                    v-model="localFilters.category"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    @change="applyFilters"
                                >
                                    <option value="">All Categories</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.slug">
                                        {{ category.name }} ({{ category.products_count }})
                                    </option>
                                </select>
                            </div>

                            <!-- Brand Filter -->
                            <div>
                                <select
                                    v-model="localFilters.brand"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    @change="applyFilters"
                                >
                                    <option value="">All Brands</option>
                                    <option v-for="brand in brands" :key="brand.id" :value="brand.slug">
                                        {{ brand.name }} ({{ brand.products_count }})
                                    </option>
                                </select>
                            </div>

                            <!-- Sort -->
                            <div>
                                <select
                                    v-model="localFilters.sort"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    @change="applyFilters"
                                >
                                    <option value="">Sort By</option>
                                    <option value="price_low">Price: Low to High</option>
                                    <option value="price_high">Price: High to Low</option>
                                    <option value="name">Name: A to Z</option>
                                    <option value="created_at">Newest</option>
                                </select>
                            </div>
                        </div>

                        <!-- Price Range -->
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <input
                                v-model.number="localFilters.min_price"
                                type="number"
                                placeholder="Min Price"
                                class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                @input="applyFilters"
                            />
                            <input
                                v-model.number="localFilters.max_price"
                                type="number"
                                placeholder="Max Price"
                                class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                @input="applyFilters"
                            />
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Products</h3>
                        <div v-if="products.data.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="product in products.data" :key="product.id" class="border rounded-lg p-4 hover:shadow-lg transition-shadow">
                                <img
                                    :src="product.image || '/images/placeholder.png'"
                                    :alt="product.name"
                                    class="w-full h-48 object-cover rounded-md mb-4"
                                />
                                <h4 class="font-semibold text-gray-800">{{ product.name }}</h4>
                                <p class="text-gray-600 text-sm">{{ product.category?.name }}</p>
                                <p class="text-gray-600 text-sm">{{ product.brand?.name }}</p>
                                <p class="text-indigo-600 font-bold mt-2">LKR {{ product.price }}</p>
                                <Link
                                    :href="route('customer.products.show', product.id)"
                                    class="mt-2 inline-block bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    View Details
                                </Link>
                            </div>
                        </div>
                        <div v-else class="text-center text-gray-600">
                            No products found matching your criteria.
                        </div>

                        <!-- Pagination -->
                        <div v-if="products.data.length" class="mt-6">
                            <div class="flex justify-between items-center">
                                <button
                                    :disabled="!products.prev_page_url"
                                    class="px-4 py-2 bg-gray-200 rounded-md disabled:opacity-50"
                                    @click="changePage(products.prev_page_url)"
                                >
                                    Previous
                                </button>
                                <span>Page {{ products.current_page }} of {{ products.last_page }}</span>
                                <button
                                    :disabled="!products.next_page_url"
                                    class="px-4 py-2 bg-gray-200 rounded-md disabled:opacity-50"
                                    @click="changePage(products.next_page_url)"
                                >
                                    Next
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
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

interface User {
    name: string;
}

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
}

interface PaginatedProducts {
    data: Product[];
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

defineProps<{
    user: User;
    products: PaginatedProducts;
    categories: Category[];
    brands: Brand[];
    filters: {
        search?: string;
        category?: string;
        brand?: string;
        min_price?: number;
        max_price?: number;
        sort?: string;
    };
}>();

const localFilters = ref({
    search: '',
    category: '',
    brand: '',
    min_price: null,
    max_price: null,
    sort: '',
});

watch(
    () => localFilters.value,
    () => applyFilters(),
    { deep: true }
);

const applyFilters = debounce(() => {
    router.get(
        route('products.index'),
        localFilters.value,
        { preserveState: true, preserveScroll: true }
    );
}, 300);

const changePage = (url: string | null) => {
    if (url) {
        router.get(url, {}, { preserveState: true, preserveScroll: true });
    }
};
</script>
