<script setup lang="ts">
import { Star } from 'lucide-vue-next'

defineProps<{
  products: any[]
  sortBy: string
}>()

defineEmits<{
  (e: 'update:sortBy', value: string): void
   (e: 'selectProduct', product: any): void

}>()
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-3xl font-bold tracking-tight">Products</h2>
      <div class="flex items-center space-x-2">
        <select
          :value="sortBy"
          @change="$emit('update:sortBy', ($event.target as HTMLSelectElement).value)"
          class="flex h-9 items-center justify-between rounded-md border border-[#19140035] bg-[#FDFDFC] px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-[#1915014a] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:focus:ring-[#62605b]"
        >
          <option value="name">Name</option>
          <option value="price">Price</option>
          <option value="rating">Rating</option>
        </select>
      </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <div
        v-for="product in products"
        :key="product.id"
        class="rounded-lg border border-[#19140035] bg-[#FDFDFC] shadow-sm overflow-hidden group cursor-pointer dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
       @click="$emit('selectProduct', product)"
      >
        <div class="aspect-square overflow-hidden">
          <img
            :src="product.image"
            :alt="product.name"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
          />
        </div>
        <div class="p-4">
          <h3 class="font-semibold text-lg mb-2">{{ product.name }}</h3>
          <p class="text-[#6b7280] dark:text-[#9ca3af] text-sm mb-3 line-clamp-2">{{ product.description }}</p>
          <div class="flex items-center justify-between">
            <span class="text-2xl font-bold">${{ product.price }}</span>
            <div class="flex items-center space-x-1">
              <Star class="h-4 w-4 fill-yellow-400 text-yellow-400" />
              <span class="text-sm">{{ product.rating }}</span>
            </div>
          </div>
          <button
            class="w-full mt-4 inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-[#1915014a] bg-[#1b1b18] text-[#EDEDEC] hover:bg-[#1b1b18]/90 dark:bg-[#EDEDEC] dark:text-[#0a0a0a] dark:hover:bg-[#EDEDEC]/90 h-9 px-4 py-2"
          >
            Add to Cart
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>