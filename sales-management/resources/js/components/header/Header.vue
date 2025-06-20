<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { Search, ShoppingCart } from 'lucide-vue-next'
import { usePage } from '@inertiajs/vue3'

defineProps<{
  searchQuery: string
  cartItemsCount: number
  currentView: string
}>()

defineEmits<{
  (e: 'update:searchQuery', value: string): void
  (e: 'update:currentView', value: string): void
}>()

const page = usePage()
const user = page.props.auth.user
</script>

<template>
  <header class="sticky top-0 z-50 w-full border-b bg-[#FDFDFC]/95 backdrop-blur supports-[backdrop-filter]:bg-[#FDFDFC]/60 dark:bg-[#0a0a0a]/95 dark:border-[#3E3E3A]">
    <div class="container mx-auto flex h-16 items-center justify-between px-4">
      <div class="flex items-center space-x-4">
        <h1 class="text-2xl font-bold">ShopVue</h1>
        <nav class="hidden md:flex items-center space-x-6 text-sm font-medium">
          <button
            @click="$emit('update:currentView', 'products')"
            :class="currentView === 'products' ? 'text-[#1b1b18] dark:text-[#EDEDEC]' : 'text-[#6b7280] hover:text-[#1b1b18] dark:text-[#9ca3af] dark:hover:text-[#EDEDEC]'"
          >
            Products
          </button>
        </nav>
      </div>
      <div class="flex items-center space-x-4">
        <div class="relative hidden sm:block">
          <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-[#6b7280] dark:text-[#9ca3af]" />
          <input
            :value="searchQuery"
            @input="$emit('update:searchQuery', ($event.target as HTMLInputElement).value)"
            type="search"
            placeholder="Search products..."
            class="flex h-9 w-full rounded-md border border-[#19140035] bg-[#FDFDFC] px-8 py-1 text-sm shadow-sm transition-colors placeholder:text-[#6b7280] focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-[#1915014a] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:placeholder:text-[#9ca3af] dark:focus-visible:ring-[#62605b] sm:w-64"
          />
        </div>
        <button
          @click="$emit('update:currentView', 'cart')"
          class="relative inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-[#1915014a] hover:bg-[#19140014] dark:hover:bg-[#3E3E3A] h-9 w-9"
        >
          <ShoppingCart class="h-4 w-4" />
          <span v-if="cartItemsCount > 0" class="absolute -top-2 -right-2 h-5 w-5 rounded-full bg-[#1b1b18] text-xs text-[#EDEDEC] flex items-center justify-center dark:bg-[#EDEDEC] dark:text-[#0a0a0a]">
            {{ cartItemsCount }}
          </span>
        </button>
        <nav class="flex items-center gap-4">
          <Link
            v-if="user"
            :href="route('dashboard')"
            class="inline-flex items-center justify-center rounded-sm border border-[#19140035] px-5 py-1.5 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-[#1915014a] text-[#1b1b18] hover:bg-[#19140014] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:bg-[#3E3E3A]"
          >
            Dashboard
          </Link>
          <template v-else>
            <Link
              :href="route('login')"
              class="inline-flex items-center justify-center rounded-sm border border-transparent px-5 py-1.5 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-[#1915014a] text-[#1b1b18] hover:bg-[#19140014] dark:text-[#EDEDEC] dark:hover:bg-[#3E3E3A]"
            >
              Log in
            </Link>
            <Link
              :href="route('register')"
              class="inline-flex items-center justify-center rounded-sm border border-[#19140035] px-5 py-1.5 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-[#1915014a] text-[#1b1b18] hover:bg-[#19140014] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:bg-[#3E3E3A]"
            >
              Register
            </Link>
          </template>
        </nav>
      </div>
    </div>
  </header>
</template>
