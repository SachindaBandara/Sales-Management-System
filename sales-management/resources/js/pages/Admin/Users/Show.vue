<template>
    <Head title="User Details" />
    <AuthenticatedLayout>
        <template #header>
            <HeaderSection :user="user" />
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-1 space-y-6">
                        <UserProfileCard :user="user" />
                        <QuickActionsCard :user="user" @toggle-status="toggleUserStatus" @reset-password="resetPassword" @delete-user="deleteUser" />
                    </div>
                    <div class="lg:col-span-2 space-y-6">
                        <UserDetailsCard :user="user" />
                        <ActivityLogCard />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import UserProfileCard from '@/components/Admin/Users/UserProfileCard.vue';
import QuickActionsCard from '@/components/Admin/Users/QuickActionsCard.vue';
import UserDetailsCard from '@/components/Admin/Users/UserDetailsCard.vue';
import ActivityLogCard from '@/components/Admin/Users/ActivityLogCard.vue';
import HeaderSection from '@/components/Admin/Users/HeaderSection.vue';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    is_active: boolean;
    last_login_at: string | null;
    created_at: string;
    updated_at: string;
}

const { user } = defineProps<{
    user: User;
}>();

const toggleUserStatus = () => {
    if (confirm(`Are you sure you want to ${user.is_active ? 'deactivate' : 'activate'} this user?`)) {
        router.patch(route('admin.users.toggle-status', user.id), {
            is_active: !user.is_active
        }, {
            preserveScroll: true,
            onSuccess: () => {
                // Handle success
            }
        });
    }
};

const resetPassword = () => {
    if (confirm('Are you sure you want to reset this user\'s password? They will receive an email with reset instructions.')) {
        router.post(route('admin.users.reset-password', user.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                // Handle success
            }
        });
    }
};

const deleteUser = () => {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        router.delete(route('admin.users.destroy', user.id), {
            onSuccess: () => {
                // Redirect will be handled by the controller
            }
        });
    }
};
</script>
