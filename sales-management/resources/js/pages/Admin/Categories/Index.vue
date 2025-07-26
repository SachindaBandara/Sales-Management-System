<template>
  <Head title="Category Management" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl leading-tight font-semibold text-gray-800">Category Management</h2>
        <div class="flex space-x-3">
          <ImportButton @click="openImportModal" />
          <ExportButton :is-exporting="isExporting" @click="exportCategories" />
          <AddButton label="Add New Category" @click="handleAdd" />
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Import Results Banner -->
        <ImportResultsBanner
          :results="import_results"
          success-label="Categories created"
          update-label="Categories updated"
          @show-errors="showErrors"
        />

        <!-- Filters -->
        <Filters
          :filters="form"
          @search="search"
          @clear="clearFilters"
          @filter-change="updateFilters"
        />

        <!-- Categories Table -->
        <CategoriesTable
          :categories="categories.data"
          :pagination="categories"
          @view="viewCategory"
          @edit="editCategory"
          @toggle-status="toggleStatus"
          @delete="deleteCategory"
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
import AuthenticatedLayout from '../../../layouts/AuthenticatedLayout.vue';
import AddButton from '@/components/Common/AddButton.vue';
import ExportButton from '@/components/Common/ExportButton.vue';
import ImportButton from '@/components/Common/ImportButton.vue';
import ImportResultsBanner from '@/components/Common/ImportResultsBanner.vue';
import ImportErrorsModal from '@/components/Common/ImportErrorsModal.vue';
import Filters from '@/components/Admin/Categories/SearchFilters.vue';
import CategoriesTable from '@/components/Admin/Categories/CategoriesTable.vue';
import ImportModal from '@/components/Admin/Categories/ImportModal.vue';

interface Category {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  image: string | null;
  parent_id: number | null;
  parent: Category | null;
  sort_order: number;
  is_active: boolean;
  created_at: string;
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface Categories {
  data: Category[];
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
  categories: Categories;
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

const importForm = useForm({
  import_file: null as File | null,
});

const updateFilters = (filters: Filters) => {
  form.search = filters.search || '';
  form.status = filters.status || '';
};

const search = () => {
  router.get(route('admin.categories.index'), form, {
    preserveState: true,
    replace: true,
  });
};

const clearFilters = () => {
  form.search = '';
  form.status = '';
  search();
};

const viewCategory = (category: Category) => {
  router.get(route('admin.categories.show', category.id));
};

const editCategory = (category: Category) => {
  router.get(route('admin.categories.edit', category.id));
};

const toggleStatus = (category: Category) => {
  if (confirm(`Are you sure you want to ${category.is_active ? 'deactivate' : 'activate'} this category?`)) {
    router.patch(route('admin.categories.toggle-status', category.id));
  }
};

const deleteCategory = (category: Category) => {
  if (confirm('Are you sure you want to delete this category? This action cannot be undone.')) {
    router.delete(route('admin.categories.destroy', category.id));
  }
};

const paginate = (url: string) => {
  router.get(url, {}, { preserveState: true });
};

const exportCategories = () => {
  if (isExporting.value) return;

  isExporting.value = true;

  const params = new URLSearchParams();
  if (form.search) params.append('search', form.search);
  if (form.status !== '') params.append('status', form.status);

  const exportUrl = route('admin.categories.export') + (params.toString() ? '?' + params.toString() : '');

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

const openImportModal = () => {
  showImportModal.value = true;
  importForm.clearErrors();
};

const closeImportModal = () => {
  showImportModal.value = false;
  importForm.reset();
};

const submitImport = (file: File) => {
  if (!file) {
    alert('Please select a file to import.');
    return;
  }

  isImporting.value = true;

  importForm.import_file = file;
  importForm.post(route('admin.categories.import'), {
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
  window.open(route('admin.categories.template.download'), '_blank');
};

const showErrors = async () => {
  try {
    const response = await fetch(route('admin.categories.import-errors'));
    const errors = await response.json();
    importErrors.value = errors;
    showImportErrors.value = true;
  } catch (error) {
    console.error('Failed to fetch import errors:', error);
  }
};

const handleAdd = () => {
  router.get(route('admin.categories.create'));
};

onMounted(() => {
  if (props.import_results && props.import_results.error_count > 0) {
    showErrors();
  }
});
</script>
