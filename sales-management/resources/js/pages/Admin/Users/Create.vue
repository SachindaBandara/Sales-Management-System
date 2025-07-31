<template>
    <Head title="Create User" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Create New User
                </h2>
                <FormButton
                    :href="route('admin.users.index')"
                    variant="secondary"
                >
                    Back to Users
                </FormButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <Card>
                    <CardHeader>
                        <CardTitle>Create New User</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <UserForm :errors="errors" />
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from '../../../layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import FormButton from '@/components/Admin/Users/FormButton.vue';
import UserForm from '@/components/Admin/Users/UserForm.vue';

defineProps<{
    errors?: Record<string, string>;
}>();
</script>
