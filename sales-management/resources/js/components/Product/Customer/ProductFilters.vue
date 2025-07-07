<template>
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
                        @input="$emit('apply-filters')"
                    />
                </div>

                <!-- Category Filter -->
                <div>
                    <select
                        v-model="localFilters.category"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        @change="$emit('apply-filters')"
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
                        @change="$emit('apply-filters')"
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
                        @change="$emit('apply-filters')"
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
                    @input="$emit('apply-filters')"
                />
                <input
                    v-model.number="localFilters.max_price"
                    type="number"
                    placeholder="Max Price"
                    class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    @input="$emit('apply-filters')"
                />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { defineProps, defineEmits } from 'vue';

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

interface Filters {
    search?: string;
    category?: string;
    brand?: string;
    min_price?: number;
    max_price?: number;
    sort?: string;
}

defineProps<{
    categories: Category[];
    brands: Brand[];
    filters: Filters;
}>();

const localFilters = defineModel<Filters>('filters', { required: true });

defineEmits(['apply-filters']);
</script>
