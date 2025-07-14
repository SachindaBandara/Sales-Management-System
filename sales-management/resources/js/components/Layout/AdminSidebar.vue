<template>
    <aside
        :class="[
            'fixed inset-y-0 left-0 z-50 w-64 transform border-r bg-card transition-transform duration-300 ease-in-out lg:translate-x-0',
            sidebarOpen ? 'translate-x-0' : '-translate-x-full',
        ]"
    >
        <div class="flex h-full flex-col">
            <!-- Sidebar Header -->
            <div class="flex h-16 items-center border-b px-6">
                <Link :href="route('dashboard')" class="flex items-center space-x-2">
                    <ApplicationLogo class="h-8 w-8" />
                    <span class="text-lg font-semibold">Admin Panel</span>
                </Link>
            </div>

            <!-- Sidebar Navigation -->
            <nav class="flex-1 space-y-1 p-4">
                <SidebarLink
                    :href="route('admin.dashboard')"
                    :active="route().current('admin.dashboard')"
                    icon="LayoutDashboard"
                >
                    Dashboard
                </SidebarLink>
                <SidebarLink
                    :href="route('admin.users.index')"
                    :active="route().current('admin.users.*')"
                    icon="Users"
                >
                    Users
                </SidebarLink>
                <SidebarLink
                    :href="route('admin.brands.index')"
                    :active="route().current('admin.brands.*')"
                    icon="Users"
                >
                    Brands
                </SidebarLink>
                <SidebarLink
                    :href="route('admin.categories.index')"
                    :active="route().current('admin.categories.*')"
                    icon="Users"
                >
                    Categories
                </SidebarLink>
                <SidebarLink
                    :href="route('admin.products.index')"
                    :active="route().current('admin.products.*')"
                    icon="Users"
                >
                    Products
                </SidebarLink>
                   <SidebarLink
                    :href="route('admin.orders.index')"
                    :active="route().current('admin.orders.*')"
                    icon="Users"
                >
                    Orders
                </SidebarLink>
            </nav>
        </div>
    </aside>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/components/ApplicationLogo.vue';
import SidebarLink from '@/components/SidebarLink.vue';
import { defineProps, defineEmits } from 'vue';

defineProps<{
    sidebarOpen: boolean;
}>();

defineEmits(['toggle-sidebar']);
</script>
