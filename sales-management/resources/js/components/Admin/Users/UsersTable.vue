<template>
  <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th v-for="column in columns" 
                :key="column.key"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              {{ column.label }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="user in sortedUsers" 
              :key="user.id"
              :class="getUserRowClass(user)"
              class="transition-colors duration-150">
            
            <!-- User Column -->
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-10 w-10">
                  <div :class="getAvatarClass(user)"
                       class="h-10 w-10 rounded-full flex items-center justify-center font-semibold text-sm">
                    {{ getUserInitial(user.name) }}
                  </div>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900 flex items-center">
                    {{ user.name }}
                    <span v-if="user.role === 'admin'" class="ml-2 text-purple-600">
                      <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 11.586l4.293-4.293a1 1 0 011.414 1.414l-5 5z" clip-rule="evenodd"/>
                      </svg>
                    </span>
                  </div>
                  <div class="text-sm text-gray-500">{{ user.email }}</div>
                </div>
              </div>
            </td>

            <!-- Role Column -->
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="getRoleBadgeClass(user.role)"
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                {{ user.role }}
              </span>
            </td>

            <!-- Status Column -->
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="getStatusBadgeClass(user.is_active)"
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                {{ user.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>

            <!-- Date Column -->
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ formatDate(user.created_at) }}
            </td>

            <!-- Actions Column -->
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <div class="flex space-x-2">
                <button @click="$emit('view', user)"
                        class="text-blue-600 hover:text-blue-900 transition-colors duration-150">
                  View
                </button>
                <button @click="$emit('edit', user)"
                        class="text-green-600 hover:text-green-900 transition-colors duration-150">
                  Edit
                </button>
                <button @click="$emit('toggle-status', user)"
                        class="text-yellow-600 hover:text-yellow-900 transition-colors duration-150">
                  {{ user.is_active ? 'Deactivate' : 'Activate' }}
                </button>
                <button @click="$emit('delete', user)"
                        class="text-red-600 hover:text-red-900 transition-colors duration-150"
                        :disabled="user.role === 'admin'">
                  Delete
                </button>
              </div>
            </td>
          </tr>

          <!-- Empty State -->
          <tr v-if="sortedUsers.length === 0">
            <td :colspan="columns.length + 1" class="px-6 py-8 text-center text-gray-500">
              {{ emptyMessage }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination" class="px-6 py-4 border-t bg-gray-50">
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
        </div>
        <div class="flex space-x-1">
          <button v-for="link in pagination.links" 
                  :key="link.label"
                  @click="link.url && $emit('paginate', link.url)"
                  :disabled="!link.url || link.active"
                  :class="getPaginationLinkClass(link)"
                  class="px-3 py-2 text-sm border rounded transition-colors duration-150">
            <span v-if="link.label === '&laquo; Previous'">&laquo; Previous</span>
            <span v-else-if="link.label === 'Next &raquo;'">Next &raquo;</span>
            <span v-else>{{ link.label.replace(/(<([^>]+)>)/gi, '') }}</span>
          </button>
        </div>
      </div>
    </div>
    
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

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

interface Pagination {
  from: number;
  to: number;
  total: number;
  links: PaginationLink[];
}

interface TableColumn {
  key: string;
  label: string;
}

interface Props {
  users: User[];
  pagination?: Pagination;
  columns?: TableColumn[];
  emptyMessage?: string;
  sortAdminsFirst?: boolean;
}

interface Emits {
  (e: 'view', user: User): void;
  (e: 'edit', user: User): void;
  (e: 'toggle-status', user: User): void;
  (e: 'delete', user: User): void;
  (e: 'paginate', url: string): void;
}

const props = withDefaults(defineProps<Props>(), {
  columns: () => [
    { key: 'user', label: 'User' },
    { key: 'role', label: 'Role' },
    { key: 'status', label: 'Status' },
    { key: 'created_at', label: 'Joined' }
  ],
  emptyMessage: 'No users found matching your criteria.',
  sortAdminsFirst: true
});

defineEmits<Emits>();

const sortedUsers = computed(() => {
  if (!props.sortAdminsFirst) return props.users;
  
  return [...props.users].sort((a, b) => {
    if (a.role === 'admin' && b.role !== 'admin') return -1;
    if (a.role !== 'admin' && b.role === 'admin') return 1;
    return a.name.localeCompare(b.name);
  });
});

const getUserRowClass = (user: User): string => {
  return user.role === 'admin' ? 'bg-purple-50' : 'hover:bg-gray-50';
};

const getAvatarClass = (user: User): string => {
  return user.role === 'admin' 
    ? 'bg-purple-100 text-purple-600' 
    : 'bg-blue-100 text-blue-600';
};

const getRoleBadgeClass = (role: string): string => {
  return role === 'admin' 
    ? 'bg-purple-100 text-purple-800 ring-1 ring-purple-600' 
    : 'bg-blue-100 text-blue-800';
};

const getStatusBadgeClass = (isActive: boolean): string => {
  return isActive 
    ? 'bg-green-100 text-green-800' 
    : 'bg-red-100 text-red-800';
};

const getPaginationLinkClass = (link: PaginationLink): string => {
  const baseClass = 'px-3 py-2 text-sm border rounded transition-colors duration-150';
  if (link.active) return `${baseClass} bg-blue-500 text-white border-blue-500`;
  if (!link.url) return `${baseClass} pointer-events-none opacity-50 bg-white text-gray-700 border-gray-300`;
  return `${baseClass} bg-white text-gray-700 border-gray-300 hover:bg-gray-50`;
};

const getUserInitial = (name: string): string => {
  return name.charAt(0).toUpperCase();
};

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};
</script>