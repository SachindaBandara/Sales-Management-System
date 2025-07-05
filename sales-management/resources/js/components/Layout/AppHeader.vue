<template>
    <header
        class="sticky top-0 z-40 border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60"
    >
        <div class="flex h-16 items-center px-4 sm:px-6 lg:px-8">
            <!-- Mobile Sidebar Toggle (Admin only) -->
            <Button
                v-if="$page.props.auth.user.is_admin"
                variant="ghost"
                size="icon"
                class="lg:hidden"
                @click="$emit('toggle-sidebar')"
            >
                <Menu class="h-5 w-5" />
            </Button>

            <!-- Logo (for customers or mobile admin) -->
            <div
                v-if="!$page.props.auth.user.is_admin || ($page.props.auth.user.is_admin && sidebarOpen)"
                class="flex items-center space-x-2"
                :class="[$page.props.auth.user.is_admin ? 'lg:hidden' : '']"
            >
                <Link :href="route('dashboard')" class="flex items-center space-x-2">
                    <ApplicationLogo class="h-8 w-8" />
                    <span class="text-lg font-semibold">App Name</span>
                </Link>
            </div>

            <!-- Customer Navigation -->
            <nav v-if="$page.props.auth.user.is_customer" class="ml-8 hidden space-x-6 md:flex">
                <NavLink :href="route('customer.dashboard')" :active="route().current('customer.dashboard')">
                    Dashboard
                </NavLink>

                 <NavLink :href="route('customer.home')" :active="route().current('customer.home')">
                    Home
                </NavLink>
            </nav>

            <!-- Spacer -->
            <div class="flex-1" />

            <!-- User Menu -->
            <UserMenu />
        </div>

        <!-- Mobile Navigation -->
        <div v-if="mobileMenuOpen" class="border-t bg-background md:hidden">
            <div class="space-y-1 px-4 py-3">
                <MobileNavLink
                    v-if="$page.props.auth.user.is_admin"
                    :href="route('admin.dashboard')"
                    :active="route().current('admin.dashboard')"
                >
                    Dashboard
                </MobileNavLink>
                <MobileNavLink
                    v-if="$page.props.auth.user.is_admin"
                    :href="route('admin.users.index')"
                    :active="route().current('admin.users.*')"
                >
                    Users
                </MobileNavLink>
                <MobileNavLink
                    v-if="$page.props.auth.user.is_customer"
                    :href="route('customer.dashboard')"
                    :active="route().current('customer.dashboard')"
                >
                    Dashboard
                </MobileNavLink>
                <MobileNavLink
                    v-if="$page.props.auth.user.is_customer"
                    :href="route('customer.home')"
                    :active="route().current('customer.home')"
                >
                    Home
                </MobileNavLink>
            </div>
        </div>
    </header>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/components/ApplicationLogo.vue';
import Button from '@/components/ui/button/Button.vue';
import NavLink from '@/components/NavLink.vue';
import MobileNavLink from '@/components/MobileNavLink.vue';
import UserMenu from '@/components/Layout/UserMenu.vue';
import { Menu } from 'lucide-vue-next';
import { defineProps, defineEmits } from 'vue';

defineProps<{
    sidebarOpen: boolean;
    mobileMenuOpen: boolean;
}>();

defineEmits(['toggle-sidebar', 'toggle-mobile-menu']);
</script>
