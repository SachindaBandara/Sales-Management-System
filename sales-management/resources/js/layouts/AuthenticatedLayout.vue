<template>
    <div class="min-h-screen bg-background">
        <!-- Admin Sidebar -->
        <AdminSidebar
            v-if="$page.props.auth.user.is_admin"
            :sidebar-open="sidebarOpen"
            @toggle-sidebar="sidebarOpen = !sidebarOpen"
        />

        <!-- Main Content -->
        <div :class="[$page.props.auth.user.is_admin ? 'lg:ml-64' : '']">
            <!-- Header -->
            <AppHeader
                :sidebar-open="sidebarOpen"
                :mobile-menu-open="mobileMenuOpen"
                @toggle-sidebar="sidebarOpen = !sidebarOpen"
                @toggle-mobile-menu="mobileMenuOpen = !mobileMenuOpen"
            />

            <!-- Page Header -->
            <div v-if="$slots.header" class="border-b bg-muted/40">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </div>

            <!-- Flash Messages -->
            <FlashMessages />

            <!-- Main Content -->
            <main class="flex-1">
                <slot />
            </main>
        </div>

        <!-- Mobile Sidebar Overlay -->
        <div
            v-if="sidebarOpen && $page.props.auth.user.is_admin"
            class="fixed inset-0 z-40 bg-background/80 backdrop-blur-sm lg:hidden"
            @click="sidebarOpen = false"
        />
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import AdminSidebar from '@/components/Layout/AdminSidebar.vue';
import AppHeader from '@/components/Layout/AppHeader.vue';
import FlashMessages from '@/components/Layout/FlashMessages.vue';

const sidebarOpen = ref(false);
const mobileMenuOpen = ref(false);
</script>
