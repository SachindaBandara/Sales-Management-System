<template>
  <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
    <div class="p-6">
      <form @submit.prevent="handleSearch" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <input
            v-model="localFilters.search"
            type="text"
            :placeholder="searchPlaceholder"
            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
        <div v-if="showRoleFilter">
          <select 
            v-model="localFilters.role" 
            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">All Roles</option>
            <option v-for="role in roleOptions" :key="role.value" :value="role.value">
              {{ role.label }}
            </option>
          </select>
        </div>
        <div v-if="showStatusFilter">
          <select 
            v-model="localFilters.status" 
            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="">All Status</option>
            <option v-for="status in statusOptions" :key="status.value" :value="status.value">
              {{ status.label }}
            </option>
          </select>
        </div>
        <div class="flex gap-2">
          <button 
            type="submit" 
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200"
          >
            Search
          </button>
          <button 
            type="button" 
            @click="handleClear" 
            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200"
          >
            Clear
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';

interface FilterOption {
  value: string;
  label: string;
}

interface Filters {
  search?: string;
  role?: string;
  status?: string;
}

interface Props {
  filters: Filters;
  searchPlaceholder?: string;
  showRoleFilter?: boolean;
  showStatusFilter?: boolean;
  roleOptions?: FilterOption[];
  statusOptions?: FilterOption[];
}

interface Emits {
  (e: 'search', filters: Filters): void;
  (e: 'clear'): void;
}

const props = withDefaults(defineProps<Props>(), {
  searchPlaceholder: 'Search...',
  showRoleFilter: true,
  showStatusFilter: true,
  roleOptions: () => [
    { value: 'admin', label: 'Admin' },
    { value: 'customer', label: 'Customer' }
  ],
  statusOptions: () => [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' }
  ]
});

const emit = defineEmits<Emits>();

const localFilters = ref<Filters>({
  search: props.filters.search || '',
  role: props.filters.role || '',
  status: props.filters.status || ''
});

watch(() => props.filters, (newFilters) => {
  localFilters.value = {
    search: newFilters.search || '',
    role: newFilters.role || '',
    status: newFilters.status || ''
  };
}, { deep: true });

const handleSearch = () => {
  emit('search', { ...localFilters.value });
};

const handleClear = () => {
  localFilters.value = {
    search: '',
    role: '',
    status: ''
  };
  emit('clear');
};
</script>
