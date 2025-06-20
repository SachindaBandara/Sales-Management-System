<script setup lang="ts">
import { ref } from 'vue'
import { Star, ArrowLeft } from 'lucide-vue-next'

defineProps<{
  product: any
}>()

defineEmits<{
  (e: 'back'): void
  (e: 'addToCart', product: any, quantity: number): void
}>()

const selectedQuantity = ref(1)
</script>

<template>
  <div>
    <button
      @click="$emit('back')"
      class="inline-flex items-center text-sm text-[#6b7280] hover:text-[#1b1b18] dark:text-[#9ca3af] dark:hover:text-[#EDEDEC] mb-6"
    >
      <ArrowLeft class="h-4 w-4 mr-2" />
      Back to Products
    </button>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <div class="aspect-square overflow-hidden rounded-lg">
        <img
          :src="product.image"
          :alt="product.name"
          class="w-full h-full object-cover"
        />
      </div>
      <div class="space-y-6">
        <div>
          <h1 class="text-3xl font-bold">{{ product.name }}</h1>
          <div class="flex items-center space-x-2 mt-2">
            <div class="flex items-center space-x-1">
              <Star v-for="i in 5" :key="i" :class="i <= product.rating ? 'h-4 w-4 fill-yellow-400 text-yellow-400' : 'h-4 w-4 text-gray-300'" />
            </div>
            <span class="text-sm text-[#6b7280] dark:text-[#9ca3af]">({{ product.reviews }} reviews)</span>
          </div>
        </div>
        <div class="text-3xl font-bold">${{ product.price }}</div>
        <p class="text-[#6b7280] dark:text-[#9ca3af]">{{ product.description }}</p>
        <div class="space-y-4">
          <div>
            <label class="text-sm font-medium">Quantity</label>
            <select
              v-model="selectedQuantity"
              class="flex h-9 w-20 items-center justify-between rounded-md border border-[#19140035] bg-[#FDFDFC] px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-[#1915014a] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:focus:ring-[#62605b] mt-1"
            >
              <option v-for="i in 10" :key="i" :value="i">{{ i }}</option>
            </select>
          </div>
          <button
            @click="$emit('addToCart', product, selectedQuantity)"
            class="w-full inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-[#1915014a] bg-[#1b1b18] text-[#EDEDEC] hover:bg-[#1b1b18]/90 dark:bg-[#EDEDEC] dark:text-[#0a0a0a] dark:hover:bg-[#EDEDEC]/90 h-10 px-4 py-2"
          >
            Add to Cart
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
