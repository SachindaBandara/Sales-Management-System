<script setup lang="ts">
import AuthenticatedLayout from '../../../layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { reactive, computed, ref, onMounted } from 'vue';
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

interface ImportError {
    row: number;
    errors: string[];
}

interface ImportResults {
    success_count: number;
    update_count: number;
    error_count: number;
    total_processed: number;
}

const props = defineProps<{
    products: Products;
    brands: Brand[];
    categories: Category[];
    filters: Filters;
     flash?: {
        success?: string;
        error?: string;
        warning?: string;
    };
    import_results?: ImportResults;
}>();

const form = reactive({
    search: props.filters.search || '',
    status: props.filters.status || '',
    brand_id: props.filters.brand_id || '',
    category_id: props.filters.category_id || '',
});

const isExporting = ref(false);
const isImporting = ref(false);
const showImportModal = ref(false);
const showImportErrors = ref(false);
const importErrors = ref<ImportError[]>([]);
const importFile = ref<File | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

// Import form using Inertia's useForm
const importForm = useForm({
    import_file: null as File | null
});

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

const openImportModal = () => {
    showImportModal.value = true;
    importFile.value = null;
    importForm.clearErrors();
};

const closeImportModal = () => {
    showImportModal.value = false;
    importFile.value = null;
    importForm.reset();
};

const onFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        importFile.value = target.files[0];
        importForm.import_file = target.files[0];
    }
};

const submitImport = () => {
    if (!importForm.import_file) {
        alert('Please select a file to import.');
        return;
    }

    isImporting.value = true;

    importForm.post(route('admin.products.import'), {
        onSuccess: () => {
            closeImportModal();
            isImporting.value = false;
        },
        onError: () => {
            isImporting.value = false;
        },
        onFinish: () => {
            isImporting.value = false;
        }
    });
};

const downloadTemplate = () => {
    window.open(route('admin.products.template.download'), '_blank');
};

const showErrors = async () => {
    try {
        const response = await fetch(route('admin.products.import-errors'));
        const errors = await response.json();
        importErrors.value = errors;
        showImportErrors.value = true;
    } catch (error) {
        console.error('Failed to fetch import errors:', error);
    }
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
        currency: 'LKR',
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

onMounted(() => {
    // Check if there are import results to potentially show errors
    if (props.import_results && props.import_results.error_count > 0) {
        // Auto-fetch errors if there were import errors
        showErrors();
    }
});
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
                    <!-- Import Button -->
                    <button 
                        @click="openImportModal"
                        class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200 flex items-center space-x-2"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                        </svg>
                        <span>Import Excel</span>
                    </button>
                    
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
                  <!-- Import Results Banner -->
                <div v-if="import_results" class="mb-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="h-5 w-5 text-blue-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="flex-1">
                                <div class="text-blue-700 text-sm font-medium">Import Results Summary</div>
                                <div class="text-blue-600 text-sm mt-1">
                                    <span v-if="import_results.success_count > 0" class="mr-4">✅ {{ import_results.success_count }} new brands created</span>
                                    <span v-if="import_results.update_count > 0" class="mr-4">🔄 {{ import_results.update_count }} brands updated</span>
                                    <span v-if="import_results.error_count > 0" class="mr-4 text-red-600">❌ {{ import_results.error_count }} errors</span>
                                </div>
                                <button v-if="import_results.error_count > 0" @click="showErrors" class="text-blue-800 text-sm underline mt-1">
                                    View Error Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
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

         <!-- Import Modal -->
        <div v-if="showImportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Import Brands from Excel</h3>
                        <button @click="closeImportModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                        <h4 class="text-sm font-medium text-blue-900 mb-2">Excel Format Requirements:</h4>
                        <ul class="text-sm text-blue-800 space-y-1">
                            <li>• <strong>Name</strong> (Required): Brand name</li>
                            <li>• <strong>Slug</strong> (Optional): URL-friendly version (auto-generated if empty)</li>
                            <li>• <strong>Description</strong> (Optional): Brand description</li>
                            <li>• <strong>Logo URL</strong> (Optional): Direct URL to brand logo</li>
                            <li>• <strong>Status</strong> (Optional): active/inactive, 1/0, true/false, yes/no</li>
                        </ul>
                        <div class="mt-3">
                            <button @click="downloadTemplate" class="text-blue-600 hover:text-blue-800 text-sm underline">
                                📄 Download Excel Template
                            </button>
                        </div>
                    </div>

                    <form @submit.prevent="submitImport" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Select Excel File
                            </label>
                            <input
                                ref="fileInput"
                                type="file"
                                accept=".xlsx,.xls,.csv"
                                @change="onFileChange"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            />
                            <p class="mt-1 text-sm text-gray-500">Supported formats: .xlsx, .xls, .csv (Max: 10MB)</p>
                            <div v-if="importForm.errors.import_file" class="mt-1 text-sm text-red-600">
                                {{ importForm.errors.import_file }}
                            </div>
                        </div>

                        <div v-if="importFile" class="p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-gray-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                </svg>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ importFile.name }}</div>
                                    <div class="text-sm text-gray-500">{{ Math.round(importFile.size / 1024) }} KB</div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 pt-4">
                            <button
                                type="button"
                                @click="closeImportModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors duration-200"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="!importFile || isImporting"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:bg-blue-300 disabled:cursor-not-allowed transition-colors duration-200 flex items-center space-x-2"
                            >
                                <svg v-if="isImporting" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>{{ isImporting ? 'Importing...' : 'Import Brands' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Import Errors Modal -->
        <div v-if="showImportErrors" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 xl:w-1/2 shadow-lg rounded-md bg-white max-h-3/4 overflow-y-auto">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-red-900">Import Errors</h3>
                        <button @click="showImportErrors = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        <div v-for="error in importErrors" :key="`error-${error.row}`" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                            <div class="font-medium text-red-900 text-sm mb-1">Row {{ error.row }}</div>
                            <ul class="text-red-800 text-sm space-y-1">
                                <li v-for="errorMsg in error.errors" :key="errorMsg">• {{ errorMsg }}</li>
                            </ul>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button
                            @click="showImportErrors = false"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 transition-colors duration-200"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
