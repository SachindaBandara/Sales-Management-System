<template>
    <Head title="Admin Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Admin Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <StatsCard
                        :value="stats.total_customers"
                        title="Total Customers"
                        color="text-blue-600"
                    />
                    <StatsCard
                        :value="stats.active_customers"
                        title="Active Customers"
                        color="text-green-600"
                    />
                    <StatsCard
                        :value="stats.total_admins"
                        title="Total Admins"
                        color="text-purple-600"
                    />
                    <StatsCard
                        title="Manage Users"
                        description="View all users"
                        :is-link="true"
                        :href="route('admin.users.index')"
                        color="text-blue-600 hover:text-blue-800"
                    />
                </div>
                <RecentRegistrationsTable :users="stats.recent_registrations" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import StatsCard from '@/components/Common/StatsCard.vue';
import RecentRegistrationsTable from '@/components/Admin/Users/RecentRegistrationsTable.vue';

interface User {
    id: number;
    name: string;
    email: string;
    created_at: string;
}

interface Stats {
    total_customers: number;
    active_customers: number;
    total_admins: number;
    recent_registrations: User[];
}

defineProps<{
    stats: Stats;
}>();
</script>
