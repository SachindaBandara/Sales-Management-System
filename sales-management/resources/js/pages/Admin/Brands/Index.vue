<template>
  <Head title="Brand Management" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl leading-tight font-semibold text-gray-800">Brand Management</h2>
        <div class="flex space-x-3">
          <ImportButton @click="openImportModal" />
          <ExportButton :is-exporting="isExporting" @click="exportBrands" />
          <AddButton label="Add Brand" @click="handleAdd" />
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Import Results Banner -->
        <ImportResultsBanner
          :results="import_results"
          success-label="brands created"
          update-label="brands updated"
          @show-errors="showErrors"
        />

        <!-- Filters -->
        <Filters
          :filters="form"
          @search="search"
          @clear="clearFilters"
          @filter-change="updateFilters"
        />

        <!-- Brands Table -->
        <BrandsTable
          :brands="brands.data"
          :pagination="brands"
          @view="viewBrand"
          @edit="editBrand"
          @toggle-status="toggleStatus"
          @delete="deleteBrand"
          @paginate="paginate"
        />

        <!-- Import Modal -->
        <ImportModal
          :show="showImportModal"
          :is-submitting="isImporting"
          :file-error="importForm.errors.import_file"
          @close="closeImportModal"
          @submit="submitImport"
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

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import Filters from '@/components/Admin/Brands/SearchFilters.vue';
import BrandsTable from '@/components/Admin/Brands/BrandsTable.vue';
import ImportModal from '@/components/Admin/Brands/ImportModal.vue';
import ImportErrorsModal from '@/components/Common/ImportErrorsModal.vue';
import ImportResultsBanner from '@/components/Common/ImportResultsBanner.vue';
import AddButton from '@/components/Common/AddButton.vue';
import ExportButton from '@/components/Common/ExportButton.vue';
import ImportButton from '@/components/Common/ImportButton.vue';

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

const form = reactive<Filters>({
  search: props.filters.search ?? '',
  status: props.filters.status ?? '',
});

const isExporting = ref(false);
const isImporting = ref(false);
const showImportModal = ref(false);
const showImportErrors = ref(false);
const importErrors = ref<ImportError[]>([]);
const importForm = useForm({
  import_file: null as File | null,
});

// Reactive brands data
const updateFilters = (filters: Filters) => {
  form.search = filters.search;
  form.status = filters.status;
};
// Search function
const search = () => {
  router.get(route('admin.brands.index'), form, {
    preserveState: true,
    replace: true,
  });
};
// Clear filters function
const clearFilters = () => {
  form.search = '';
  form.status = '';
  search();
};
// Toggle brand status function
const toggleStatus = (brand: Brand) => {
  if (confirm(`Are you sure you want to ${brand.is_active ? 'deactivate' : 'activate'} this brand?`)) {
    router.patch(route('admin.brands.toggle-status', brand.id));
  }
};
// Delete brand function
const deleteBrand = (brand: Brand) => {
  if (confirm('Are you sure you want to delete this brand? This action cannot be undone.')) {
    router.delete(route('admin.brands.destroy', brand.id));
  }
};
// View and edit brand functions
const viewBrand = (brand: Brand) => {
  router.get(route('admin.brands.show', brand.id));
};
// Edit brand function
const editBrand = (brand: Brand) => {
  router.get(route('admin.brands.edit', brand.id));
};
// Paginate function
const paginate = (url: string) => {
  router.get(url, {}, { preserveState: true });
};
// Export brands function
const exportBrands = () => {
  if (isExporting.value) return;

  isExporting.value = true;

  const params = new URLSearchParams();
  if (form.search) params.append('search', form.search);
  if (form.status) params.append('status', form.status);

  const exportUrl = route('admin.brands.export') + (params.toString() ? '?' + params.toString() : '');

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
// Open import modal function
const openImportModal = () => {
  showImportModal.value = true;
  importForm.reset();
};
// Close import modal function
const closeImportModal = () => {
  showImportModal.value = false;
  importForm.reset();
};
// Submit import function
const submitImport = (file: File) => {
  importForm.import_file = file;
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
    },
  });
};
// Download template function
const downloadTemplate = () => {
  window.open(route('admin.brands.template.download'), '_blank');
};
// Show import errors function
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
// Handle add brand function
const handleAdd = () => {
  router.get(route('admin.brands.create'));
};
// Show import errors on mount if there are any
onMounted(() => {
  if (props.import_results && props.import_results.error_count > 0) {
    showErrors();
  }
});
</script>
