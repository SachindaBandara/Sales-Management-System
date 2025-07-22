<script setup lang="ts">
import AuthenticatedLayout from '../../../layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { reactive, computed, ref, onMounted } from 'vue';

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
    brands: Brands;
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

const exportBrands = () => {
    if (isExporting.value) return;
    
    isExporting.value = true;
    
    // Create URL with current filters
    const params = new URLSearchParams();
    if (form.search) params.append('search', form.search);
    if (form.status !== '') params.append('status', form.status);
    
    const exportUrl = route('admin.brands.export') + (params.toString() ? '?' + params.toString() : '');
    
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
    
    importForm.post(route('admin.brands.import'), {
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
    window.open(route('admin.brands.template.download'), '_blank');
};

const showErrors = async () => {
    try {
        const response = await fetch(route('admin.brands.import-errors'));
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

onMounted(() => {
    // Check if there are import results to potentially show errors
    if (props.import_results && props.import_results.error_count > 0) {
        // Auto-fetch errors if there were import errors
        showErrors();
    }
});
</script>

<template>
    <Head title="Brand Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Brand Management
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
                        @click="exportBrands"
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
                    
                    <!-- Add New Brand Button -->
                    <Link :href="route('admin.brands.create')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Add New Brand
                    </Link>
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

                <!-- Export Info Banner (shown when filters are applied) -->
                <div v-if="form.search || form.status !== ''" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <div class="text-blue-700 text-sm">
                            <strong>Export Note:</strong> The Excel export will include brands matching your current filters
                            <span v-if="form.search">(Search: "{{ form.search }}")</span>
                            <span v-if="form.status !== ''">(Status: {{ form.status === '1' ? 'Active' : 'Inactive' }})</span>.
                        </div>
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