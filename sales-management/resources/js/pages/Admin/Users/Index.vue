<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl leading-tight font-semibold text-gray-800">User Management</h2>
                <div class="flex space-x-3">
                    <ImportButton @click="openImportModal" />
                    <ExportButton :is-exporting="isExporting" @click="exportUsers" />
                    <AddButton label="Add User" @click="handleAdd" />
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="!isLoading" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <StatsCard
                        :value="stats?.total_customers ?? 0"
                        title="Total Customers"
                        color="text-blue-600"
                    />
                      <StatsCard
                        :value="stats?.total_admins ?? 0"
                        title="Total Admins"
                        color="text-purple-600"
                    />
                    <StatsCard
                        :value="stats?.active_customers ?? 0"
                        title="Active Customers"
                        color="text-green-600"
                    />
                    <StatsCard
                        :value="stats?.inactive_customers ?? 0"
                        title="Inactive Customers"
                        color="text-red-600"
                    />
                </div>

                <!-- Import Results -->
                <ImportResultsBanner :results="import_results" success-label="users created" update-label="users updated" @show-errors="showErrors" />

                <!-- Filters -->
                <SearchFilters :filters="filters" search-placeholder="Search by name or email..." @search="handleSearch" @clear="clearFilters" />

                <!-- Users Table -->
                <UsersTable
                    :users="users.data"
                    :pagination="users"
                    @view="viewUser"
                    @edit="editUser"
                    @toggle-status="openToggleStatusDialog"
                    @delete="openDeleteDialog"
                    @paginate="handlePagination"
                />

                <!-- Import Modal -->
                <ImportModal
                    :show="showImportModal"
                    title="Import Users from Excel"
                    :requirements="importRequirements"
                    template-button-text="Download User Template"
                    file-label="Select Excel File"
                    submit-text="Import Users"
                    submitting-text="Importing Users..."
                    :is-submitting="isImporting"
                    :file-error="importForm.errors.import_file"
                    @close="closeImportModal"
                    @submit="submitImport"
                    @download-template="downloadTemplate"
                />

                <!-- Import Errors Modal -->
                <ImportErrorsModal :show="showImportErrors" :errors="importErrors" @close="showImportErrors = false" />

                <!-- Confirm Dialogs -->
                <ConfirmDialog
                    ref="toggleStatusDialog"
                    :title="toggleDialogTitle"
                    :description="toggleDialogDescription"
                    :confirm-text="toggleDialogConfirmText"
                    @confirm="confirmToggleStatus"
                />
                <ConfirmDialog
                    ref="deleteDialog"
                    title="Delete User"
                    description="Are you sure you want to delete this user? This action cannot be undone."
                    confirm-text="Delete"
                    confirm-variant="destructive"
                    @confirm="confirmDelete"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { onMounted, reactive, ref } from 'vue';
import AuthenticatedLayout from '../../../layouts/AuthenticatedLayout.vue';
import ConfirmDialog from '@/components/Common/ConfirmDialog.vue';

// Import components
import AddButton from '@/components/Common/AddButton.vue';
import ExportButton from '@/components/Common/ExportButton.vue';
import ImportButton from '@/components/Common/ImportButton.vue';
import ImportErrorsModal from '@/components/Common/ImportErrorsModal.vue';
import ImportModal from '@/components/Admin/Users/ImportModal.vue';
import ImportResultsBanner from '@/components/Common/ImportResultsBanner.vue';
import SearchFilters from '@/components/Admin/Users/SearchFilters.vue';
import UsersTable from '@/components/Admin/Users/UsersTable.vue';
import StatsCard from '@/components/Common/StatsCard.vue';

// Types
interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    is_active: boolean;
    created_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Users {
    data: User[];
    from: number;
    to: number;
    total: number;
    links: PaginationLink[];
}

interface Filters {
    search?: string;
    role?: string;
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

interface Stats {
    total_customers: number;
    active_customers: number;
    inactive_customers: number;
    total_admins: number;
}

const props = defineProps<{
    users: Users;
    filters: Filters;
    flash?: {
        success?: string;
        error?: string;
        warning?: string;
    };
    import_results?: ImportResults;
    stats?: Stats; // Made stats optional
}>();

// Reactive data
const form = reactive({
    search: props.filters.search || '',
    role: props.filters.role || '',
    status: props.filters.status || '',
});

const isExporting = ref(false);
const isImporting = ref(false);
const showImportModal = ref(false);
const showImportErrors = ref(false);
const importErrors = ref<ImportError[]>([]);
const selectedUser = ref<User | null>(null);
const toggleStatusDialog = ref<InstanceType<typeof ConfirmDialog> | null>(null);
const deleteDialog = ref<InstanceType<typeof ConfirmDialog> | null>(null);
const toggleDialogTitle = ref('');
const toggleDialogDescription = ref('');
const toggleDialogConfirmText = ref('');

// Import form
const importForm = useForm({
    import_file: null as File | null,
});

// Import requirements
const importRequirements = [
    'Name (Required): User full name',
    'Email (Required): Valid email address',
    'Role (Optional): admin/customer (defaults to customer)',
    'Password (Optional): User password (auto-generated if empty)',
    'Status (Optional): active/inactive, 1/0, true/false, yes/no',
];

// Methods
const handleSearch = (filters: Filters) => {
    Object.assign(form, filters);
    router.get(route('admin.users.index'), form, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    form.search = '';
    form.role = '';
    form.status = '';
    handleSearch(form);
};

const viewUser = (user: User) => {
    router.visit(route('admin.users.show', user.id));
};

const editUser = (user: User) => {
    router.visit(route('admin.users.edit', user.id));
};

const openToggleStatusDialog = (user: User) => {
    selectedUser.value = user;
    toggleDialogTitle.value = user.is_active ? 'Deactivate User' : 'Activate User';
    toggleDialogDescription.value = `Are you sure you want to ${user.is_active ? 'deactivate' : 'activate'} ${user.name}?`;
    toggleDialogConfirmText.value = user.is_active ? 'Deactivate' : 'Activate';
    toggleStatusDialog.value?.open();
};

const confirmToggleStatus = () => {
    if (selectedUser.value) {
        router.patch(route('admin.users.toggle-status', selectedUser.value.id));
    }
};

const openDeleteDialog = (user: User) => {
    selectedUser.value = user;
    deleteDialog.value?.open();
};

const confirmDelete = () => {
    if (selectedUser.value) {
        router.delete(route('admin.users.destroy', selectedUser.value.id));
    }
};

const handlePagination = (url: string) => {
    if (url) {
        router.visit(url);
    }
};

const exportUsers = () => {
    if (isExporting.value) return;

    isExporting.value = true;

    const params = new URLSearchParams();
    if (form.search) params.append('search', form.search);
    if (form.status !== '') params.append('status', form.status);

    const exportUrl = route('admin.users.export') + (params.toString() ? '?' + params.toString() : '');

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
    importForm.import_file = file;
    isImporting.value = true;

    importForm.post(route('admin.users.import'), {
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
    window.open(route('admin.users.template.download'), '_blank');
};

const showErrors = async () => {
    try {
        const response = await fetch(route('admin.users.import-errors'));
        const errors = await response.json();
        importErrors.value = errors;
        showImportErrors.value = true;
    } catch (error) {
        console.error('Failed to fetch import errors:', error);
    }
};

const handleAdd = () => {
    router.get(route('admin.users.create'));
};

const isLoading = ref(true);

onMounted(() => {
    if (props.import_results && props.import_results.error_count > 0) {
        showErrors();
    }
    isLoading.value = false;
});
</script>
