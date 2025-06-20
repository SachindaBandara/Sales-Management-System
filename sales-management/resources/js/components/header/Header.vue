<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Search, ShoppingCart } from 'lucide-vue-next';

const page = usePage();
const user = page.props.auth.user;
</script>

<template>
    <header
        class="sticky top-0 z-50 w-full border-b bg-[#FDFDFC]/95 backdrop-blur supports-[backdrop-filter]:bg-[#FDFDFC]/60 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]/95"
    >
        <div class="container mx-auto flex h-16 items-center justify-between px-4">
            <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-bold">ShopVue</h1>
                <nav class="hidden items-center space-x-6 text-sm font-medium md:flex">
                    <button :class="'text-[#1b1b18] dark:text-[#EDEDEC]'">Products</button>
                </nav>
            </div>
            <div class="flex items-center space-x-4">
                <div class="relative hidden sm:block">
                    <Search class="absolute top-2.5 left-2.5 h-4 w-4 text-[#6b7280] dark:text-[#9ca3af]" />
                    <input
                        type="search"
                        placeholder="Search products..."
                        class="flex h-9 w-full rounded-md border border-[#19140035] bg-[#FDFDFC] px-8 py-1 text-sm shadow-sm transition-colors placeholder:text-[#6b7280] focus-visible:ring-1 focus-visible:ring-[#1915014a] focus-visible:outline-none sm:w-64 dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:placeholder:text-[#9ca3af] dark:focus-visible:ring-[#62605b]"
                    />
                </div>
            <button
                    class="relative inline-flex h-9 w-9 items-center justify-center rounded-md text-sm font-medium transition-colors hover:bg-[#19140014] focus-visible:ring-1 focus-visible:ring-[#1915014a] focus-visible:outline-none dark:hover:bg-[#3E3E3A]"
                >
                    <ShoppingCart class="h-4 w-4" />
                </button>
                <nav class="flex items-center gap-4">
                    <Link
                        v-if="user"
                        :href="route('dashboard')"
                        class="inline-flex items-center justify-center rounded-sm border border-[#19140035] px-5 py-1.5 text-sm font-medium text-[#1b1b18] transition-colors hover:bg-[#19140014] focus-visible:ring-1 focus-visible:ring-[#1915014a] focus-visible:outline-none dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:bg-[#3E3E3A]"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="inline-flex items-center justify-center rounded-sm border border-transparent px-5 py-1.5 text-sm font-medium text-[#1b1b18] transition-colors hover:bg-[#19140014] focus-visible:ring-1 focus-visible:ring-[#1915014a] focus-visible:outline-none dark:text-[#EDEDEC] dark:hover:bg-[#3E3E3A]"
                        >
                            Log in
                        </Link>
                        <Link
                            :href="route('register')"
                            class="inline-flex items-center justify-center rounded-sm border border-[#19140035] px-5 py-1.5 text-sm font-medium text-[#1b1b18] transition-colors hover:bg-[#19140014] focus-visible:ring-1 focus-visible:ring-[#1915014a] focus-visible:outline-none dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:bg-[#3E3E3A]"
                        >
                            Register
                        </Link>
                    </template>
                </nav>
            </div>
        </div>
    </header>
</template>
