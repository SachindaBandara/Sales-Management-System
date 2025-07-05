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

                <!-- Filters Component -->
                <ProductFilters
                    :categories="categories"
                    :brands="brands"
                    :filters="localFilters"
                    @apply-filters="applyFilters"
                />

                <!-- Products Grid -->
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Products</h3>
                        <div v-if="products.data.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            <ProductCard
                                v-for="product in products.data"
                                :key="product.id"
                                :product="product"
                                :adding-to-cart="addingToCart[product.id]"
                                @add-to-cart="addToCart"
                            />
                        </div>
                        <div v-else class="text-center text-gray-600">
                            No products found matching your criteria.
                        </div>

                        <!-- Pagination Component -->
                        <Pagination
                            v-if="products.data.length"
                            :products="products"
                            @change-page="changePage"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';
import ProductFilters from '@/components/Product/ProductFilters.vue';
import ProductCard from '@/components/Product/ProductCard.vue';
import Pagination from '@/components/Product/Pagination.vue';

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
    is_in_stock: boolean;
}

interface PaginatedProducts {
    data: Product[];
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

interface Filters {
    search?: string;
    category?: string;
    brand?: string;
    min_price?: number;
    max_price?: number;
    sort?: string;
}

defineProps<{
    user: User;
    products: PaginatedProducts;
    categories: Category[];
    brands: Brand[];
    filters: Filters;
}>();

const localFilters = ref<Filters>({
    search: '',
    category: '',
    brand: '',
    min_price: undefined,
    max_price: undefined,
    sort: '',
});

const addingToCart = ref<{ [key: number]: boolean }>({});

watch(
    () => localFilters.value,
    () => applyFilters(),
    { deep: true }
);

const applyFilters = debounce(() => {
    router.get(
        route('customer.products.index'),
        localFilters.value,
        { preserveState: true, preserveScroll: true }
    );
}, 300);

const changePage = (url: string | null) => {
    if (url) {
        router.get(url, {}, { preserveState: true, preserveScroll: true });
    }
};

const addToCart = (productId: number) => {
    addingToCart.value[productId] = true;

    router.post(
        route('customer.cart.store'),
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
</script>
