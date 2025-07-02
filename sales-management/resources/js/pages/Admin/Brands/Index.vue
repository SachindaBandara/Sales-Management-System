<script setup lang="ts">
import AuthenticatedLayout from '../../../layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, computed } from 'vue';

interface Brand {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    logo: string | null;
    is_active: boolean;
    created_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Brands {
    data: Brand[];
    from: number;
    to: number;
    total: number;
    links: PaginationLink[];
}

interface Filters {
    search?: string;
    status?: string;
}

const props = defineProps<{
    brands: Brands;
    filters: Filters;
}>();

const form = reactive({
    search: props.filters.search || '',
    status: props.filters.status || '',
});

// Computed property to sort brands alphabetically by name
const sortedBrands = computed(() => {
    return [...props.brands.data].sort((a, b) => a.name.localeCompare(b.name));
});

const search = () => {
    router.get(route('admin.brands.index'), form, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    form.search = '';
    form.status = '';
    search();
};

const toggleStatus = (brand: Brand) => {
    if (confirm(`Are you sure you want to ${brand.is_active ? 'deactivate' : 'activate'} this brand?`)) {
        router.patch(route('admin.brands.toggle-status', brand.id));
    }
};

const deleteBrand = (brand: Brand) => {
    if (confirm('Are you sure you want to delete this brand? This action cannot be undone.')) {
        router.delete(route('admin.brands.destroy', brand.id));
    }
};

const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>

<template>
    <Head title="Brand Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Brand Management
                </h2>
                <Link :href="route('admin.brands.create')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Add New Brand
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <form @submit.prevent="search" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <input
                                    v-model="form.search"
                                    type="text"
                                    placeholder="Search by name..."
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                            <div>
                                <select v-model="form.status" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">All Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                                    Search
                                </button>
                                <button type="button" @click="clearFilters" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                                    Clear
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Brands Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Brand
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="brand in sortedBrands" :key="brand.id"
                                    class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img v-if="brand.logo" :src="brand.logo" class="h-10 w-10 rounded-full object-cover" alt="Brand logo" />
                                                <div v-else class="h-10 w-10 rounded-full flex items-center justify-center bg-blue-100 text-blue-600 font-semibold text-sm">
                                                    {{ brand.name.charAt(0).toUpperCase() }}
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ brand.name }}
                                                </div>
                                                <div v-if="brand.description" class="text-sm text-gray-500 truncate max-w-xs">
                                                    {{ brand.description }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ brand.slug }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="brand.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                              class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ brand.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(brand.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <Link :href="route('admin.brands.show', brand.id)"
                                                  class="text-blue-600 hover:text-blue-900 transition-colors duration-150">
                                                View
                                            </Link>
                                            <Link :href="route('admin.brands.edit', brand.id)"
                                                  class="text-green-600 hover:text-green-900 transition-colors duration-150">
                                                Edit
                                            </Link>
                                            <button @click="toggleStatus(brand)"
                                                    class="text-yellow-600 hover:text-yellow-900 transition-colors duration-150">
                                                {{ brand.is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                            <button @click="deleteBrand(brand)"
                                                    class="text-red-600 hover:text-red-900 transition-colors duration-150">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="sortedBrands.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        No brands found matching your criteria.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing {{ brands.from }} to {{ brands.to }} of {{ brands.total }} results
                            </div>
                            <div class="flex space-x-1">
                                <Link v-for="link in brands.links" :key="link.label"
                                      :href="link.url ?? ''"
                                      :class="[
                                          'px-3 py-2 text-sm border rounded transition-colors duration-150',
                                          link.active ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                                          !link.url ? 'pointer-events-none opacity-50' : ''
                                      ]"
                                >
                                    <span v-if="link.label === '« Previous'">« Previous</span>
                                    <span v-else-if="link.label === 'Next »'">Next »</span>
                                    <span v-else>{{ link.label.replace(/(<([^>]+)>)/gi, '') }}</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>