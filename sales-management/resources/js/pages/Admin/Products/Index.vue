<script setup lang="ts">
import AuthenticatedLayout from '../../../layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, computed, ref } from 'vue';
import axios from 'axios';

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
    short_description: string | null;
    sku: string;
    price: number;
    compare_price: number | null;
    stock_quantity: number;
    track_quantity: boolean;
    images: string[];
    image_urls: string[];
    first_image_url: string;
    image_count: number;
    status: string;
    brand: Brand | null;
    category: Category | null;
    created_at: string;
    discount_percentage: number;
    is_in_stock: boolean;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Products {
    data: Product[];
    from: number;
    to: number;
    total: number;
    links: PaginationLink[];
}

interface Filters {
    search?: string;
    status?: string;
    brand_id?: string;
    category_id?: string;
}

const props = defineProps<{
    products: Products;
    brands: Brand[];
    categories: Category[];
    filters: Filters;
}>();

const form = reactive({
    search: props.filters.search || '',
    status: props.filters.status || '',
    brand_id: props.filters.brand_id || '',
    category_id: props.filters.category_id || '',
});

const isExporting = ref(false);

// Selected products for bulk actions
const selectedProducts = ref<number[]>([]);
const isLoading = ref(false);
const showBulkActions = ref(false);

// Computed property to sort products by created_at (newest first)
const sortedProducts = computed(() => {
    return [...props.products.data].sort((a, b) =>
        new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
    );
});

const allSelected = computed(() => {
    return sortedProducts.value.length > 0 && selectedProducts.value.length === sortedProducts.value.length;
});

const someSelected = computed(() => {
    return selectedProducts.value.length > 0 && selectedProducts.value.length < sortedProducts.value.length;
});

const search = () => {
    router.get(route('admin.products.index'), form, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    form.search = '';
    form.status = '';
    form.brand_id = '';
    form.category_id = '';
    search();
};

const deleteProduct = (product: Product) => {
    if (confirm('Are you sure you want to delete this product? This action cannot be undone and will remove all associated images.')) {
        router.delete(route('admin.products.destroy', product.id));
    }
};

const toggleProductStatus = async (product: Product) => {
    try {
        await axios.patch(route('admin.products.toggle-status', product.id));
        router.reload({ only: ['products'] });
    } catch (error) {
        console.error('Error toggling product status:', error);
    }
};

const duplicateProduct = async (product: Product) => {
    if (confirm('Are you sure you want to duplicate this product?')) {
        try {
            await axios.post(route('admin.products.duplicate', product.id));
            router.reload({ only: ['products'] });
        } catch (error) {
            console.error('Error duplicating product:', error);
        }
    }
};

// Bulk actions
const toggleSelectAll = () => {
    if (allSelected.value) {
        selectedProducts.value = [];
    } else {
        selectedProducts.value = sortedProducts.value.map(p => p.id);
    }
};

const toggleProductSelection = (productId: number) => {
    const index = selectedProducts.value.indexOf(productId);
    if (index > -1) {
        selectedProducts.value.splice(index, 1);
    } else {
        selectedProducts.value.push(productId);
    }
};

const executeBulkAction = async (action: string) => {
    if (selectedProducts.value.length === 0) {
        alert('Please select at least one product.');
        return;
    }

    const confirmMessage = {
        'delete': 'Are you sure you want to delete the selected products? This action cannot be undone.',
        'activate': 'Are you sure you want to activate the selected products?',
        'deactivate': 'Are you sure you want to deactivate the selected products?',
        'archive': 'Are you sure you want to archive the selected products?',
    }[action];

    if (confirm(confirmMessage)) {
        try {
            isLoading.value = true;
            await axios.post(route('admin.products.bulk-action'), {
                action,
                product_ids: selectedProducts.value
            });
            selectedProducts.value = [];
            showBulkActions.value = false;
            router.reload({ only: ['products'] });
        } catch (error) {
            console.error('Error executing bulk action:', error);
        } finally {
            isLoading.value = false;
        }
    }
};

// Image management
const deleteAllImages = async (product: Product) => {
    if (confirm('Are you sure you want to delete all images for this product? This action cannot be undone.')) {
        try {
            await axios.delete(route('admin.products.images.delete-all', product.id));
            router.reload({ only: ['products'] });
        } catch (error) {
            console.error('Error deleting all images:', error);
        }
    }
};

const viewImages = (product: Product) => {
    // This could open a modal or navigate to an image management page
    router.visit(route('admin.products.show', product.id));
};

const exportProducts = () => {
    if (isExporting.value) return;
    
    isExporting.value = true;
    
    // Create URL with current filters
    const params = new URLSearchParams();
    if (form.search) params.append('search', form.search);
    if (form.status !== '') params.append('status', form.status);
    
    const exportUrl = route('admin.products.export') + (params.toString() ? '?' + params.toString() : '');
    
    // Create a temporary link to trigger download
    const link = document.createElement('a');
    link.href = exportUrl;
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Reset loading state after a short delay
    setTimeout(() => {
        isExporting.value = false;
    }, 2000);
};


const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const formatPrice = (price: number): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2
    }).format(price);
};

const getImageUrl = (product: Product): string => {
    // First try to get from image_urls array
    if (product.image_urls && product.image_urls.length > 0) {
        return product.image_urls[0];
    }

    // Then try first_image_url
    if (product.first_image_url) {
        return product.first_image_url;
    }

    // Finally try images array (fallback)
    if (product.images && product.images.length > 0) {
        return `/storage/${product.images[0]}`;
    }

    // Return placeholder if no image
    return '/images/placeholder.jpg';
};

const handleImageError = (event: Event) => {
    const target = event.target as HTMLImageElement;
    target.src = '/images/placeholder.jpg';
};

const getStockStatusColor = (quantity: number): string => {
    if (quantity <= 5) return 'text-red-600';
    if (quantity <= 20) return 'text-yellow-600';
    return 'text-green-600';
};

const getStatusBadgeClass = (status: string): string => {
    const classes = {
        'active': 'bg-green-100 text-green-800',
        'draft': 'bg-yellow-100 text-yellow-800',
        'archived': 'bg-red-100 text-red-800'
    };
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Product Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Product Management
                </h2>
                  <div class="flex space-x-3">
                    <!-- Export Button -->
                    <button 
                        @click="exportProducts"
                        :disabled="isExporting"
                        class="bg-green-500 hover:bg-green-700 disabled:bg-green-300 text-white font-bold py-2 px-4 rounded transition-colors duration-200 flex items-center space-x-2"
                    >
                        <svg v-if="isExporting" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span>{{ isExporting ? 'Exporting...' : 'Export Excel' }}</span>
                    </button>

                <div class="flex items-center space-x-4">
                    <!-- <Link :href="route('admin.products-statistics')"
                          class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                        View Statistics
                    </Link> -->
                    <Link :href="route('admin.products.create')"
                          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                        Add New Product
                    </Link>
                </div>
            </div>
        </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <form @submit.prevent="search" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <input
                                    v-model="form.search"
                                    type="text"
                                    placeholder="Search by name or SKU..."
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                            <div>
                                <select v-model="form.status"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="draft">Draft</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                            <div>
                                <select v-model="form.brand_id"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">All Brands</option>
                                    <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                                        {{ brand.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <select v-model="form.category_id"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">All Categories</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit"
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                                    Search
                                </button>
                                <button type="button" @click="clearFilters"
                                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                                    Clear
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <div v-if="selectedProducts.length > 0" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <span class="text-sm font-medium text-blue-800">
                                {{ selectedProducts.length }} product{{ selectedProducts.length === 1 ? '' : 's' }} selected
                            </span>
                            <button @click="showBulkActions = !showBulkActions"
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Bulk Actions
                            </button>
                        </div>
                        <button @click="selectedProducts = []"
                                class="text-blue-600 hover:text-blue-800 text-sm">
                            Clear Selection
                        </button>
                    </div>

                    <div v-if="showBulkActions" class="mt-4 flex flex-wrap gap-2">
                        <button @click="executeBulkAction('activate')"
                                :disabled="isLoading"
                                class="bg-green-500 hover:bg-green-700 text-white text-sm font-medium py-2 px-4 rounded disabled:opacity-50">
                            Activate Selected
                        </button>
                        <button @click="executeBulkAction('deactivate')"
                                :disabled="isLoading"
                                class="bg-yellow-500 hover:bg-yellow-700 text-white text-sm font-medium py-2 px-4 rounded disabled:opacity-50">
                            Deactivate Selected
                        </button>
                        <button @click="executeBulkAction('archive')"
                                :disabled="isLoading"
                                class="bg-orange-500 hover:bg-orange-700 text-white text-sm font-medium py-2 px-4 rounded disabled:opacity-50">
                            Archive Selected
                        </button>
                        <button @click="executeBulkAction('delete')"
                                :disabled="isLoading"
                                class="bg-red-500 hover:bg-red-700 text-white text-sm font-medium py-2 px-4 rounded disabled:opacity-50">
                            Delete Selected
                        </button>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <input type="checkbox"
                                               :checked="allSelected"
                                               :indeterminate="someSelected"
                                               @change="toggleSelectAll"
                                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Product
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        SKU
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Price
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Stock
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Images
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Brand
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Category
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Created
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="product in sortedProducts" :key="product.id"
                                    class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox"
                                               :checked="selectedProducts.includes(product.id)"
                                               @change="toggleProductSelection(product.id)"
                                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-12 w-12 relative">
                                                <img :src="getImageUrl(product)"
                                                     @error="handleImageError"
                                                     class="h-12 w-12 rounded-lg object-cover border border-gray-200 shadow-sm"
                                                     :alt="product.name" />
                                                <div v-if="product.image_count > 1"
                                                     class="absolute -top-2 -right-2 bg-blue-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                                    {{ product.image_count }}
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ product.name }}
                                                </div>
                                                <div v-if="product.short_description"
                                                     class="text-sm text-gray-500 truncate max-w-xs">
                                                    {{ product.short_description }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 font-mono">{{ product.sku }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 font-semibold">
                                            {{ formatPrice(product.price) }}
                                        </div>
                                        <div v-if="product.compare_price && product.compare_price > product.price"
                                             class="text-sm text-gray-500">
                                            <span class="line-through">{{ formatPrice(product.compare_price) }}</span>
                                            <span class="ml-2 text-red-600 font-medium">
                                                (-{{ product.discount_percentage }}%)
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div v-if="product.track_quantity" class="text-sm">
                                            <span :class="getStockStatusColor(product.stock_quantity)" class="font-medium">
                                                {{ product.stock_quantity }}
                                            </span>
                                            <span class="text-gray-500"> units</span>
                                        </div>
                                        <div v-else class="text-sm text-gray-500">
                                            Not tracked
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm text-gray-900 font-medium">
                                                {{ product.image_count || 0 }}
                                            </span>
                                            <button v-if="product.image_count > 0"
                                                    @click="viewImages(product)"
                                                    class="text-blue-600 hover:text-blue-800 text-sm">
                                                View
                                            </button>
                                            <button v-if="product.image_count > 0"
                                                    @click="deleteAllImages(product)"
                                                    class="text-red-600 hover:text-red-800 text-sm">
                                                Clear
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ product.brand?.name ?? 'No brand' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ product.category?.name ?? 'No category' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button @click="toggleProductStatus(product)"
                                                :class="getStatusBadgeClass(product.status)"
                                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full hover:opacity-80 transition-opacity">
                                            {{ product.status.charAt(0).toUpperCase() + product.status.slice(1) }}
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(product.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex flex-col space-y-2">
                                            <div class="flex space-x-3">
                                                <Link :href="route('admin.products.show', product.id)"
                                                      class="text-blue-600 hover:text-blue-900 transition-colors duration-150">
                                                    View
                                                </Link>
                                                <Link :href="route('admin.products.edit', product.id)"
                                                      class="text-green-600 hover:text-green-900 transition-colors duration-150">
                                                    Edit
                                                </Link>
                                            </div>
                                            <div class="flex space-x-3">
                                                <button @click="duplicateProduct(product)"
                                                        class="text-indigo-600 hover:text-indigo-900 transition-colors duration-150">
                                                    Duplicate
                                                </button>
                                                <button @click="deleteProduct(product)"
                                                        class="text-red-600 hover:text-red-900 transition-colors duration-150">
                                                    Delete
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="sortedProducts.length === 0">
                                    <td colspan="11" class="px-6 py-12 text-center text-gray-500">
                                        <div class="text-lg">No products found</div>
                                        <div class="text-sm mt-2">
                                            Try adjusting your search criteria or
                                            <Link :href="route('admin.products.create')"
                                                  class="text-blue-600 hover:text-blue-800">
                                                add a new product
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing {{ products.from }} to {{ products.to }} of {{ products.total }} results
                            </div>
                            <div class="flex space-x-1">
                                <Link v-for="link in products.links" :key="link.label"
                                      :href="link.url ?? ''"
                                      :class="[
                                          'px-3 py-2 text-sm border rounded transition-colors duration-150',
                                          link.active ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                                          !link.url ? 'pointer-events-none opacity-50' : ''
                                      ]"
                                >
                                    <span v-if="link.label === '&laquo; Previous'">‹ Previous</span>
                                    <span v-else-if="link.label === 'Next &raquo;'">Next ›</span>
                                    <span v-else v-html="link.label"></span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
