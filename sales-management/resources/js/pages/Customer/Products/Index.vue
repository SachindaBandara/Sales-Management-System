<template>
    <Head title="Shop" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Welcome to Our Shop, {{ user.name }}!
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Flash Messages -->
                <div v-if="$page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash.error" class="mb-4 p-4 bg-red-100 text-red-800 rounded-md">
                    {{ $page.props.flash.error }}
                </div>

                <!-- Main Grid Layout -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Filters Section (Left Side) -->
                    <div class="lg:col-span-1 bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Filters</h3>
                            <!-- Search -->
                            <div class="mb-4">
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Products</label>
                                <input
                                    id="search"
                                    v-model="localFilters.search"
                                    type="text"
                                    placeholder="Search products..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                />
                            </div>

                            <!-- Category Filter -->
                            <div class="mb-4">
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                <select
                                    id="category"
                                    v-model="localFilters.category"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">All Categories</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.slug">
                                        {{ category.name }} ({{ category.products_count }})
                                    </option>
                                </select>
                            </div>

                            <!-- Brand Filter -->
                            <div class="mb-4">
                                <label for="brand" class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                                <select
                                    id="brand"
                                    v-model="localFilters.brand"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">All Brands</option>
                                    <option v-for="brand in brands" :key="brand.id" :value="brand.slug">
                                        {{ brand.name }} ({{ brand.products_count }})
                                    </option>
                                </select>
                            </div>

                            <!-- Sort -->
                            <div class="mb-4">
                                <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                                <select
                                    id="sort"
                                    v-model="localFilters.sort"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">Latest</option>
                                    <option value="price_low">Price: Low to High</option>
                                    <option value="price_high">Price: High to Low</option>
                                    <option value="name">Name: A to Z</option>
                                </select>
                            </div>

                            <!-- Price Range -->
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="min_price" class="block text-sm font-medium text-gray-700 mb-1">Min Price</label>
                                    <input
                                        id="min_price"
                                        v-model.number="localFilters.min_price"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        placeholder="0.00"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                    />
                                </div>
                                <div>
                                    <label for="max_price" class="block text-sm font-medium text-gray-700 mb-1">Max Price</label>
                                    <input
                                        id="max_price"
                                        v-model.number="localFilters.max_price"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        placeholder="1000.00"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                    />
                                </div>
                            </div>

                            <!-- Clear Filters -->
                            <div class="flex justify-end">
                                <button
                                    @click="clearFilters"
                                    type="button"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Clear Filters
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Products Section (Right Side) -->
                    <div class="lg:col-span-3 bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-lg font-semibold">Products</h3>
                                <div class="text-sm text-gray-600">
                                    Showing {{ products.data.length }} of {{ products.total }} products
                                </div>
                            </div>

                            <!-- Products Grid -->
                            <div v-if="products.data.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div
                                    v-for="product in products.data"
                                    :key="product.id"
                                    class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200"
                                >
                                    <!-- Product Image -->
                                    <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-t-lg bg-gray-200">
                                        <img
                                            :src="product.first_image_url || '/images/no-image.png'"
                                            :alt="product.name"
                                            class="h-48 w-full object-cover object-center group-hover:opacity-75"
                                        />
                                    </div>

                                    <!-- Product Info -->
                                    <div class="p-4">
                                        <h3 class="text-sm font-medium text-gray-900 mb-1">
                                            <Link
                                                :href="route('customer.products.show', product.id)"
                                                class="hover:text-indigo-600"
                                            >
                                                {{ product.name }}
                                            </Link>
                                        </h3>

                                        <div class="flex items-center justify-between mb-2">
                                            <p class="text-sm text-gray-500">{{ product.category?.name }}</p>
                                            <span class="text-sm text-gray-500">{{ product.brand?.name }}</span>
                                        </div>

                                        <div class="flex items-center justify-between">
                                            <span class="text-lg font-bold text-gray-900">${{ formatPrice(product.price) }}</span>
                                            <span
                                                v-if="product.is_in_stock"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                            >
                                                In Stock
                                            </span>
                                            <span
                                                v-else
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
                                            >
                                                Out of Stock
                                            </span>
                                        </div>

                                        <!-- Add to Cart Button -->
                                        <button
                                            @click="addToCart(product.id)"
                                            :disabled="!product.is_in_stock || addingToCart[product.id]"
                                            class="mt-3 w-full bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:bg-gray-300 disabled:cursor-not-allowed"
                                        >
                                            <span v-if="addingToCart[product.id]">Adding...</span>
                                            <span v-else-if="!product.is_in_stock">Out of Stock</span>
                                            <span v-else>Add to Cart</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- No Products Found -->
                            <div v-else class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2M4 13h2m8-4V8a1 1 0 011-1h3a1 1 0 011 1v1" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No products found</h3>
                                <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filter criteria.</p>
                            </div>

                            <!-- Pagination -->
                            <div v-if="products.data.length && products.last_page > 1" class="mt-8">
                                <nav class="flex items-center justify-between">
                                    <div class="flex-1 flex justify-between sm:hidden">
                                        <Link
                                            v-if="products.prev_page_url"
                                            :href="products.prev_page_url"
                                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                        >
                                            Previous
                                        </Link>
                                        <Link
                                            v-if="products.next_page_url"
                                            :href="products.next_page_url"
                                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                        >
                                            Next
                                        </Link>
                                    </div>
                                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                        <div>
                                            <p class="text-sm text-gray-700">
                                                Showing page
                                                <span class="font-medium">{{ products.current_page }}</span>
                                                of
                                                <span class="font-medium">{{ products.last_page }}</span>
                                            </p>
                                        </div>
                                        <div>
                                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                                <Link
                                                    v-if="products.prev_page_url"
                                                    :href="products.prev_page_url"
                                                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                                                >
                                                    Previous
                                                </Link>
                                                <Link
                                                    v-if="products.next_page_url"
                                                    :href="products.next_page_url"
                                                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                                                >
                                                    Next
                                                </Link>
                                            </nav>
                                        </div>
                                    </div>
                                </nav>
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
import { ref, watch, onMounted } from 'vue';
import { debounce } from 'lodash';

interface User {
    id: number;
    name: string;
    email: string;
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
    description?: string;
    price: number;
    image_urls?: string[];
    first_image_url?: string;
    category?: Category;
    brand?: Brand;
    is_in_stock: boolean;
    created_at: string;
    updated_at: string;
}

interface PaginatedProducts {
    data: Product[];
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    total: number;
    per_page: number;
    from: number;
    to: number;
}

interface Filters {
    search?: string;
    category?: string;
    brand?: string;
    min_price?: number;
    max_price?: number;
    sort?: string;
}

const props = defineProps<{
    user: User;
    products: PaginatedProducts;
    categories: Category[];
    brands: Brand[];
    filters: Filters;
}>();

// Initialize local filters with props.filters
const localFilters = ref<Filters>({
    search: props.filters.search || '',
    category: props.filters.category || '',
    brand: props.filters.brand || '',
    min_price: props.filters.min_price || undefined,
    max_price: props.filters.max_price || undefined,
    sort: props.filters.sort || '',
});

const addingToCart = ref<{ [key: number]: boolean }>({});

// Watch for changes in local filters and apply them with debouncing
watch(
    () => localFilters.value,
    debounce(() => {
        applyFilters();
    }, 500),
    { deep: true }
);

const applyFilters = () => {
    // Clean up empty values
    const cleanFilters = Object.fromEntries(
        Object.entries(localFilters.value).filter(([value]) =>
            value !== '' && value !== null && value !== undefined
        )
    );

    router.get(
        route('customer.dashboard'),
        cleanFilters,
        {
            preserveState: true,
            preserveScroll: true,
            replace: true
        }
    );
};

const clearFilters = () => {
    localFilters.value = {
        search: '',
        category: '',
        brand: '',
        min_price: undefined,
        max_price: undefined,
        sort: '',
    };
};

const addToCart = (productId: number) => {
    addingToCart.value[productId] = true;

    router.post(
        route('customer.cart.add', productId),
        {
            product_id: productId,
            quantity: 1,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                addingToCart.value[productId] = false;
            },
            onError: () => {
                addingToCart.value[productId] = false;
            },
        }
    );
};

const formatPrice = (price: number): string => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(price);
};

onMounted(() => {
    // Ensure filters are synced with URL params on component mount
});
</script>

<style scoped>
@media (max-width: 1023px) {
    .grid-cols-4 {
        grid-template-columns: 1fr;
    }
}
</style>
