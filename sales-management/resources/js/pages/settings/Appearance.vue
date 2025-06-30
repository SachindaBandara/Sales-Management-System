<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import AuthenticationLayout from '../../../js/layouts/AuthenticatedLayout.vue';
import SettingLayout from '../../layouts/settings/Layout.vue';
</script>

<template>
    <AuthenticationLayout>
        <Head title="Appearance Settings" />
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Appearance Settings</h2>
        </template>

        <SettingLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <p class="mb-4 font-medium">Update your account's appearance settings.</p>
                        <AppearanceTabs />
                    </div>
                </div>
            </div>
        </div>
        </SettingLayout>
    </AuthenticationLayout>
</template>