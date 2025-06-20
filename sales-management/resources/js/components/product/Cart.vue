<script setup lang="ts">
import { ShoppingCart, Minus, Plus, Trash2 } from 'lucide-vue-next'

defineProps<{
  cart: any[]
  cartTotal: number
}>()

defineEmits<{
  (e: 'updateQuantity', productId: number, quantity: number): void
  (e: 'removeFromCart', productId: number): void
  (e: 'proceedToCheckout'): void
  (e: 'continueShopping'): void
}>()
</script>

<template>
  <div>
    <h2 class="text-3xl font-bold tracking-tight mb-6">Shopping Cart</h2>
    <div v-if="cart.length === 0" class="text-center py-12">
      <ShoppingCart class="h-12 w-12 text-[#6b7280] dark:text-[#9ca3af] mx-auto mb-4" />
      <h3 class="text-lg font-semibold mb-2">Your cart is empty</h3>
      <p class="text-[#6b7280] dark:text-[#9ca3af] mb-4">Add some products to get started</p>
      <button
        @click="$emit('continueShopping')"
        class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-[#1915014a] bg-[#1b1b18] text-[#EDEDEC] hover:bg-[#1b1b18]/90 dark:bg-[#EDEDEC] dark:text-[#0a0a0a] dark:hover:bg-[#EDEDEC]/90 h-9 px-4 py-2"
      >
        Continue Shopping
      </button>
    </div>
    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2 space-y-4">
        <div
          v-for="item in cart"
          :key="item.id"
          class="flex items-center space-x-4 rounded-lg border border-[#19140035] bg-[#FDFDFC] p-4 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]"
        >
          <img :src="item.image" :alt="item.name" class="h-16 w-16 rounded object-cover" />
          <div class="flex-1">
            <h3 class="font-semibold">{{ item.name }}</h3>
            <p class="text-sm text-[#6b7280] dark:text-[#9ca3af]">${{ item.price }}</p>
          </div>
          <div class="flex items-center space-x-2">
            <button
              @click="$emit('updateQuantity', item.id, item.quantity - 1)"
              class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-[#1915014a] border border-[#19140035] bg-[#FDFDFC] hover:bg-[#19140014] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:hover:bg-[#3E3E3A] h-8 w-8"
            >
              <Minus class="h-4 w-4" />
            </button>
            <span class="w-8 text-center">{{ item.quantity }}</span>
            <button
              @click="$emit('updateQuantity', item.id, item.quantity + 1)"
              class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-[#1915014a] border border-[#19140035] bg-[#FDFDFC] hover:bg-[#19140014] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:hover:bg-[#3E3E3A] h-8 w-8"
            >
              <Plus class="h-4 w-4" />
            </button>
          </div>
          <button
            @click="$emit('removeFromCart', item.id)"
            class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-[#1915014a] border border-[#19140035] bg-[#FDFDFC] hover:bg-[#19140014] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:hover:bg-[#3E3E3A] h-8 w-8"
          >
            <Trash2 class="h-4 w-4" />
          </button>
        </div>
      </div>
      <div class="rounded-lg border border-[#19140035] bg-[#FDFDFC] p-6 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]">
        <h3 class="text-lg font-semibold mb-4">Order Summary</h3>
        <div class="space-y-2 mb-4">
          <div class="flex justify-between">
            <span>Subtotal</span>
            <span>${{ cartTotal.toFixed(2) }}</span>
          </div>
          <div class="flex justify-between">
            <span>Shipping</span>
            <span>$10.00</span>
          </div>
          <div class="flex justify-between">
            <span>Tax</span>
            <span>${{ (cartTotal * 0.08).toFixed(2) }}</span>
          </div>
          <div class="h-px bg-[#19140035] dark:bg-[#3E3E3A]"></div>
          <div class="flex justify-between font-semibold text-lg">
            <span>Total</span>
            <span>${{ (cartTotal + 10 + cartTotal * 0.08).toFixed(2) }}</span>
          </div>
        </div>
        <button
          @click="$emit('proceedToCheckout')"
          class="w-full inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-[#1915014a] bg-[#1b1b18] text-[#EDEDEC] hover:bg-[#1b1b18]/90 dark:bg-[#EDEDEC] dark:text-[#0a0a0a] dark:hover:bg-[#EDEDEC]/90 h-10 px-4 py-2"
        >
          Proceed to Checkout
        </button>
      </div>
    </div>
  </div>
</template>
