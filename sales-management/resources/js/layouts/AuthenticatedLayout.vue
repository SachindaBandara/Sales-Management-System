<template>
    <div class="min-h-screen bg-background">
        <!-- Admin Sidebar -->
        <aside v-if="$page.props.auth.user.is_admin" :class="[
            'fixed inset-y-0 left-0 z-50 w-64 transform border-r bg-card transition-transform duration-300 ease-in-out lg:translate-x-0',
            sidebarOpen ? 'translate-x-0' : '-translate-x-full',
        ]">
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
                    <!-- Dashboard -->
                    <SidebarLink :href="route('admin.dashboard')" :active="route().current('admin.dashboard')"
                        icon="LayoutDashboard">
                        Dashboard
                    </SidebarLink>

                    <!-- User -->
                    <SidebarLink :href="route('admin.users.index')" :active="route().current('admin.users.*')"
                        icon="Users"> Users </SidebarLink>

                    <!-- Brand -->
                    <SidebarLink :href="route('admin.brands.index')" :active="route().current('n.brands.*')"
                        icon="Users"> Brands </SidebarLink>

                    <!-- Category -->
                    <SidebarLink :href="route('admin.categories.index')" :active="route().current('admin.categories.*')"
                        icon="Users"> Categories </SidebarLink>

                    <!-- Product -->
                    <SidebarLink :href="route('admin.products.index')" :active="route().current('admin.products.*')"
                        icon="Users"> Products </SidebarLink>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <div :class="[$page.props.auth.user.is_admin ? 'lg:ml-64' : '']">
            <!-- Header -->
            <header
                class="sticky top-0 z-40 border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
                <div class="flex h-16 items-center px-4 sm:px-6 lg:px-8">
                    <!-- Mobile Sidebar Toggle (Admin only) -->
                    <Button v-if="$page.props.auth.user.is_admin" variant="ghost" size="icon" class="lg:hidden"
                        @click="sidebarOpen = !sidebarOpen">
                        <Menu class="h-5 w-5" />
                    </Button>

                    <!-- Logo (for customers or mobile admin) -->
                    <div v-if="!$page.props.auth.user.is_admin || ($page.props.auth.user.is_admin && sidebarOpen)"
                        class="flex items-center space-x-2"
                        :class="[$page.props.auth.user.is_admin ? 'lg:hidden' : '']">
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
                    <div DETAILS="flex items-center space-x-4">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" class="relative h-10 w-auto px-3">
                                    <div class="flex items-center space-x-3">
                                        <Avatar :fallback="$page.props.auth.user.name.charAt(0).toUpperCase()"
                                            size="sm" />
                                        <div class="hidden text-left sm:block">
                                            <div class="text-sm font-medium">
                                                {{ $page.props.auth.user.name }}
                                            </div>
                                            <div class="text-xs text-muted-foreground">
                                                {{ $page.props.auth.user.email }}
                                            </div>
                                        </div>
                                        <Badge variant="secondary" class="ml-2">
                                            {{ $page.props.auth.user.role }}
                                        </Badge>
                                        <ChevronDown class="h-4 w-4 text-muted-foreground" />
                                    </div>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-56">
                                <DropdownMenuLabel>
                                    <div class="flex flex-col space-y-1">
                                        <p class="text-sm font-medium">{{ $page.props.auth.user.name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ $page.props.auth.user.email }}</p>
                                    </div>
                                </DropdownMenuLabel>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem as-child>
                                    <Link :href="route('profile.edit')" class="flex items-center">
                                    <User class="mr-2 h-4 w-4" />
                                    Profile
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem as-child>
                                    <Link :href="route('logout')" method="post" as="button"
                                        class="flex w-full items-center text-red-600">
                                    <LogOut class="mr-2 h-4 w-4" />
                                    Log out
                                    </Link>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>

                <!-- Mobile Navigation -->
                <div v-if="mobileMenuOpen" class="border-t bg-background md:hidden">
                    <div class="space-y-1 px-4 py-3">
                        <MobileNavLink v-if="$page.props.auth.user.is_admin" :href="route('admin.dashboard')"
                            :active="route().current('admin.dashboard')">
                            Dashboard
                        </MobileNavLink>
                        <MobileNavLink v-if="$page.props.auth.user.is_admin" :href="route('admin.users.index')"
                            :active="route().current('admin.users.*')">
                            User
                        </MobileNavLink>
                        <MobileNavLink v-if="$page.props.auth.user.is_customer" :href="route('customer.dashboard')"
                            :active="route().current('customer.dashboard')">
                            Dashboard
                        </MobileNavLink>
                        <MobileNavLink v-if="$page.props.auth.user.is_customer" :href="route('customer.home')"
                            :active="route().current('customer.home')">
                            Home
                        </MobileNavLink>
                    </div>
                </div>
            </header>

            <!-- Page Header -->
            <div v-if="$slots.header" class="border-b bg-muted/40">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </div>

            <!-- Flash Messages -->
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <Alert v-if="$page.props.flash.success" class="mt-4" variant="default">
                    <CheckCircle class="h-4 w-4" />
                    <AlertDescription>
                        {{ $page.props.flash.success }}
                    </AlertDescription>
                </Alert>

                <Alert v-if="$page.props.flash.error" class="mt-4" variant="destructive">
                    <AlertCircle class="h-4 w-4" />
                    <AlertDescription>
                        {{ $page.props.flash.error }}
                    </AlertDescription>
                </Alert>
            </div>

            <!-- Main Content -->
            <main class="flex-1">
                <slot />
            </main>
        </div>

        <!-- Mobile Sidebar Overlay -->
        <div v-if="sidebarOpen && $page.props.auth.user.is_admin"
            class="fixed inset-0 z-40 bg-background/80 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false" />
    </div>
</template>

<script setup lang="ts">
import ApplicationLogo from '@/components/ApplicationLogo.vue';
import MobileNavLink from '@/components/MobileNavLink.vue';
import NavLink from '@/components/NavLink.vue';
import SidebarLink from '@/components/SidebarLink.vue';
import { Alert, AlertDescription } from '@/components/ui/alert';
import Avatar from '@/components/ui/avatar/Avatar.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import Button from '@/components/ui/button/Button.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Link } from '@inertiajs/vue3';
import { AlertCircle, CheckCircle, ChevronDown, LogOut, Menu, User } from 'lucide-vue-next';
import { ref } from 'vue';

const sidebarOpen = ref(false);
const mobileMenuOpen = ref(false);
</script>
