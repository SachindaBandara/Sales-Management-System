<script setup lang="ts">
import AuthenticatedLayout from '../../../layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, computed } from 'vue';

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

const props = defineProps<{
    users: Users;
    filters: Filters;
}>();

const form = reactive({
    search: props.filters.search || '',
    role: props.filters.role || '',
    status: props.filters.status || '',
});

// Computed property to sort users with admins first
const sortedUsers = computed(() => {
    return [...props.users.data].sort((a, b) => {
        // First, sort by role (admin first)
        if (a.role === 'admin' && b.role !== 'admin') return -1;
        if (a.role !== 'admin' && b.role === 'admin') return 1;

        // If same role, sort by name alphabetically
        return a.name.localeCompare(b.name);
    });
});

const search = () => {
    router.get(route('admin.users.index'), form, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    form.search = '';
    form.role = '';
    form.status = '';
    search();
};

const toggleStatus = (user: User) => {
    if (confirm(`Are you sure you want to ${user.is_active ? 'deactivate' : 'activate'} this user?`)) {
        router.patch(route('admin.users.toggle-status', user.id));
    }
};

const deleteUser = (user: User) => {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        router.delete(route('admin.users.destroy', user.id));
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
    <Head title="User Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    User Management
                </h2>
                <Link :href="route('admin.users.create')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Add New User
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <form @submit.prevent="search" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <input
                                    v-model="form.search"
                                    type="text"
                                    placeholder="Search by name or email..."
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                            <div>
                                <select v-model="form.role" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">All Roles</option>
                                    <option value="admin">Admin</option>
                                    <option value="customer">Customer</option>
                                </select>
                            </div>
                            <div>
                                <select v-model="form.status" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
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

                <!-- Users Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        User
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="user in sortedUsers" :key="user.id"
                                    :class="user.role === 'admin' ? 'bg-purple-50' : 'hover:bg-gray-50'"
                                    class="transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div :class="user.role === 'admin' ? 'bg-purple-100 text-purple-600' : 'bg-blue-100 text-blue-600'"
                                                     class="h-10 w-10 rounded-full flex items-center justify-center font-semibold text-sm">
                                                    {{ user.name.charAt(0).toUpperCase() }}
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
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="user.role === 'admin' ? 'bg-purple-100 text-purple-800 ring-1 ring-purple-600' : 'bg-blue-100 text-blue-800'"
                                              class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ user.role }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                              class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                            {{ user.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(user.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <Link :href="route('admin.users.show', user.id)"
                                                  class="text-blue-600 hover:text-blue-900 transition-colors duration-150">
                                                View
                                            </Link>
                                            <Link :href="route('admin.users.edit', user.id)"
                                                  class="text-green-600 hover:text-green-900 transition-colors duration-150">
                                                Edit
                                            </Link>
                                            <button @click="toggleStatus(user)"
                                                    class="text-yellow-600 hover:text-yellow-900 transition-colors duration-150">
                                                {{ user.is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                            <button @click="deleteUser(user)"
                                                    class="text-red-600 hover:text-red-900 transition-colors duration-150"
                                                    :disabled="user.role === 'admin'">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="sortedUsers.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        No users found matching your criteria.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing {{ users.from }} to {{ users.to }} of {{ users.total }} results
                            </div>
                            <div class="flex space-x-1">
                                <Link v-for="link in users.links" :key="link.label"
                                      :href="link.url ?? ''"
                                      :class="[
                                          'px-3 py-2 text-sm border rounded transition-colors duration-150',
                                          link.active ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                                          !link.url ? 'pointer-events-none opacity-50' : ''
                                      ]"
                                >
                                    <span v-if="link.label === '&laquo; Previous'">&laquo; Previous</span>
                                    <span v-else-if="link.label === 'Next &raquo;'">Next &raquo;</span>
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
