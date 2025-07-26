<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, reactive, ref } from 'vue';
import AuthenticatedLayout from '../../../layouts/AuthenticatedLayout.vue';

// Component imports
import AddButton from '@/components/Common/AddButton.vue';
import ExportButton from '@/components/Common/ExportButton.vue';
import ImportButton from '@/components/Common/ImportButton.vue';
import ImportErrorsModal from '@/components/Common/ImportErrorsModal.vue';
import ImportResultsBanner from '@/components/Common/ImportResultsBanner.vue';
import ProductFilters from '@/components/Admin/Products/SearchFilters.vue';
import BulkActions from '@/components/Admin/Products/BulkActions.vue';
import ProductsTable from '@/components/Admin/Products/ProductsTable.vue';
import ImportModal from '@/components/Admin/Products/ImportModal.vue';

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

// Reactive form for filters
const form = reactive({
    search: props.filters.search || '',
    status: props.filters.status || '',
    brand_id: props.filters.brand_id || '',
    category_id: props.filters.category_id || '',
});

// State management
const isExporting = ref(false);
const isImporting = ref(false);
const showImportModal = ref(false);
const showImportErrors = ref(false);
const importErrors = ref<ImportError[]>([]);
const selectedProducts = ref<number[]>([]);
const isLoading = ref(false);

// Import form using Inertia's useForm
const importForm = useForm({
    import_file: null as File | null,
});

// Bulk actions configuration
const bulkActions = [
    {
        key: 'activate',
        label: 'Activate Selected',
        variant: 'default' as const,
        confirmTitle: 'Activate Products',
        confirmMessage: 'Are you sure you want to activate the selected products?',
        confirmText: 'Activate',
        requiresConfirmation: true
    },
    {
        key: 'deactivate',
        label: 'Deactivate Selected',
        variant: 'outline' as const,
        confirmTitle: 'Deactivate Products',
        confirmMessage: 'Are you sure you want to deactivate the selected products?',
        confirmText: 'Deactivate',
        requiresConfirmation: true
    },
    {
        key: 'archive',
        label: 'Archive Selected',
        variant: 'secondary' as const,
        confirmTitle: 'Archive Products',
        confirmMessage: 'Are you sure you want to archive the selected products?',
        confirmText: 'Archive',
        requiresConfirmation: true
    },
    {
        key: 'delete',
        label: 'Delete Selected',
        variant: 'destructive' as const,
        confirmTitle: 'Delete Products',
        confirmMessage: 'Are you sure you want to delete the selected products? This action cannot be undone.',
        confirmText: 'Delete',
        requiresConfirmation: true
    }
];

// Computed properties
const sortedProducts = computed(() => {
    return [...props.products.data].sort((a, b) => 
        new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
    );
});

// Filter handlers
const handleSearch = (filters: Filters) => {
    Object.assign(form, filters);
    router.get(route('admin.products.index'), form, {
        preserveState: true,
        replace: true,
    });
};

const handleClearFilters = () => {
    form.search = '';
    form.status = '';
    form.brand_id = '';
    form.category_id = '';
    handleSearch(form);
};

const handleFilterChange = (filters: Filters) => {
    // Update form but don't search immediately
    Object.assign(form, filters);
};

// Selection handlers
const toggleSelectAll = () => {
    if (selectedProducts.value.length === sortedProducts.value.length) {
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

const clearSelection = () => {
    selectedProducts.value = [];
};

// Bulk action handler
const handleBulkAction = async (action: string, items: number[]) => {
    try {
        isLoading.value = true;
        await axios.post(route('admin.products.bulk-action'), {
            action,
            product_ids: items,
        });
        selectedProducts.value = [];
        router.reload({ only: ['products'] });
    } catch (error) {
        console.error('Error executing bulk action:', error);
    } finally {
        isLoading.value = false;
    }
};

// Product action handlers
const handleView = (product: Product) => {
    router.visit(route('admin.products.show', product.id));
};

const handleEdit = (product: Product) => {
    router.visit(route('admin.products.edit', product.id));
};

const handleDuplicate = async (product: Product) => {
    if (confirm('Are you sure you want to duplicate this product?')) {
        try {
            await axios.post(route('admin.products.duplicate', product.id));
            router.reload({ only: ['products'] });
        } catch (error) {
            console.error('Error duplicating product:', error);
        }
    }
};

const handleToggleStatus = async (product: Product) => {
    try {
        await axios.patch(route('admin.products.toggle-status', product.id));
        router.reload({ only: ['products'] });
    } catch (error) {
        console.error('Error toggling product status:', error);
    }
};

const handleDelete = (product: Product) => {
    if (confirm('Are you sure you want to delete this product? This action cannot be undone and will remove all associated images.')) {
        router.delete(route('admin.products.destroy', product.id));
    }
};

const handleViewImages = (product: Product) => {
    router.visit(route('admin.products.show', product.id));
};

const handleDeleteAllImages = async (product: Product) => {
    if (confirm('Are you sure you want to delete all images for this product? This action cannot be undone.')) {
        try {
            await axios.delete(route('admin.products.images.delete-all', product.id));
            router.reload({ only: ['products'] });
        } catch (error) {
            console.error('Error deleting all images:', error);
        }
    }
};

const handlePaginate = (url: string) => {
    router.visit(url);
};

// Export handler
const handleExport = () => {
    if (isExporting.value) return;

    isExporting.value = true;

    const params = new URLSearchParams();
    if (form.search) params.append('search', form.search);
    if (form.status !== '') params.append('status', form.status);
    if (form.brand_id !== '') params.append('brand_id', form.brand_id);
    if (form.category_id !== '') params.append('category_id', form.category_id);

    const exportUrl = route('admin.products.export') + (params.toString() ? '?' + params.toString() : '');

    const link = document.createElement('a');
    link.href = exportUrl;
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    setTimeout(() => {
        isExporting.value = false;
    }, 2000);
};

// Import handlers
const openImportModal = () => {
    showImportModal.value = true;
    importForm.clearErrors();
};

const closeImportModal = () => {
    showImportModal.value = false;
    importForm.reset();
};

const handleImportSubmit = (file: File) => {
    importForm.import_file = file;
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
        },
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

// Add product handler
const handleAdd = () => {
    router.get(route('admin.products.create'));
};

onMounted(() => {
    if (props.import_results && props.import_results.error_count > 0) {
        showErrors();
    }
});
</script>

<template>
    <Head title="Product Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl leading-tight font-semibold text-gray-800">Product Management</h2>
                <div class="flex space-x-3">
                    <ImportButton @click="openImportModal" />
                    <ExportButton :is-exporting="isExporting" @click="handleExport" />
                    <AddButton label="Add Product" @click="handleAdd" />
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Import Results Banner -->
                <ImportResultsBanner
                    :results="import_results"
                    success-label="products created"
                    update-label="products updated"
                    @show-errors="showErrors"
                />

                <!-- Filters Component -->
                <ProductFilters
                    :filters="filters"
                    :brands="brands"
                    :categories="categories"
                    @search="handleSearch"
                    @clear="handleClearFilters"
                    @filter-change="handleFilterChange"
                />

                <!-- Bulk Actions Component -->
                <BulkActions
                    :selected-items="selectedProducts"
                    :actions="bulkActions"
                    :is-loading="isLoading"
                    item-type="product"
                    @bulk-action="handleBulkAction"
                    @clear-selection="clearSelection"
                />

                <!-- Products Table Component -->
                <ProductsTable
                    :products="products.data"
                    :selected-products="selectedProducts"
                    :pagination="products"
                    @view="handleView"
                    @edit="handleEdit"
                    @duplicate="handleDuplicate"
                    @toggle-status="handleToggleStatus"
                    @delete="handleDelete"
                    @view-images="handleViewImages"
                    @delete-all-images="handleDeleteAllImages"
                    @paginate="handlePaginate"
                    @toggle-select-all="toggleSelectAll"
                    @toggle-product-selection="toggleProductSelection"
                />

                <!-- Import Modal Component -->
                <ImportModal
                    :show="showImportModal"
                    title="Import Products from Excel"
                    :requirements="[
                        'Name (Required): Product name',
                        'SKU (Optional): Product SKU (auto-generated if empty)',
                        'Price (Optional): Product price',
                        'Description (Optional): Product description',
                        'Status (Optional): active/inactive, 1/0, true/false, yes/no',
                        'Brand (Optional): Brand name',
                        'Category (Optional): Category name',
                        'Stock Quantity (Optional): Stock quantity',
                        'Compare Price (Optional): Original price for discounts',
                        'Track Quantity (Optional): true/false for inventory tracking'
                    ]"
                    template-button-text="📄 Download Excel Template"
                    submit-text="Import Products"
                    submitting-text="Importing..."
                    :is-submitting="isImporting"
                    :file-error="importForm.errors.import_file"
                    @close="closeImportModal"
                    @submit="handleImportSubmit"
                    @download-template="downloadTemplate"
                />

                <!-- Import Errors Modal -->
                <ImportErrorsModal 
                    :show="showImportErrors" 
                    :errors="importErrors" 
                    @close="showImportErrors = false" 
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Any additional component-specific styles */
</style>