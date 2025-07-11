OrderSUmmary:

<template>
  <div class="bg-white shadow rounded-lg p-6 sticky top-8">
    <h2 class="text-lg font-medium text-gray-900 mb-6">Order Summary</h2>

    <!-- Cart Items -->
    <div class="space-y-4 mb-6">
      <div
        v-for="[productId, item] in Object.entries(cart)"
        :key="productId"
        class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg"
      >
        <!-- Product Image -->
        <div class="flex-shrink-0 w-16 h-16">
          <img
            :src="item.image || '/images/placeholder.jpg'"
            :alt="item.name"
            class="w-full h-full object-cover rounded-md"
          />
        </div>

        <!-- Product Details -->
        <div class="flex-1 min-w-0">
          <h3 class="text-sm font-medium text-gray-900 truncate">{{ item.name }}</h3>
          <p v-if="item.sku" class="text-xs text-gray-500">SKU: {{ item.sku }}</p>
          <div class="flex items-center justify-between mt-1">
            <span class="text-sm text-gray-600">Qty: {{ item.quantity }}</span>
            <span class="text-sm font-medium text-gray-900">
              ${{ formatPrice(item.price) }} each
            </span>
          </div>
        </div>

        <!-- Item Total -->
        <div class="flex-shrink-0">
          <span class="text-sm font-medium text-gray-900">
            ${{ formatPrice(item.price * item.quantity) }}
          </span>
        </div>
      </div>
    </div>

    <!-- Order Totals -->
    <div class="border-t border-gray-200 pt-6">
      <div class="space-y-3">
        <!-- Subtotal -->
        <div class="flex justify-between">
          <span class="text-sm text-gray-600">Subtotal</span>
          <span class="text-sm font-medium text-gray-900">
            ${{ formatPrice(cartTotals.subtotal) }}
          </span>
        </div>

        <!-- Tax -->
        <div class="flex justify-between">
          <span class="text-sm text-gray-600">
            Tax ({{ Math.round(cartTotals.taxRate * 100) }}%)
          </span>
          <span class="text-sm font-medium text-gray-900">
            ${{ formatPrice(cartTotals.tax) }}
          </span>
        </div>

        <!-- Shipping -->
        <div class="flex justify-between">
          <span class="text-sm text-gray-600">Shipping</span>
          <span class="text-sm font-medium text-gray-900">Free</span>
        </div>

        <!-- Total -->
        <div class="flex justify-between border-t border-gray-200 pt-3">
          <span class="text-base font-medium text-gray-900">Total</span>
          <span class="text-base font-bold text-gray-900">
            ${{ formatPrice(cartTotals.total) }}
          </span>
        </div>
      </div>
    </div>

    <!-- Place Order Button -->
    <div class="mt-6">
      <button
        type="submit"
        :disabled="isProcessing"
        class="w-full bg-indigo-600 border border-transparent rounded-md shadow-sm py-3 px-4 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
        @click="$emit('submit')"
      >
        <span v-if="isProcessing" class="flex items-center justify-center">
          <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Processing...
        </span>
        <span v-else>Place Order</span>
      </button>
    </div>

    <!-- Security Notice -->
    <div class="mt-4 p-3 bg-green-50 rounded-md">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 1L5 6v6l5 5 5-5V6l-5-5zM8.5 6L10 4.5 11.5 6 13 7.5 11.5 9 10 10.5 8.5 9 7 7.5 8.5 6z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <p class="text-sm text-green-800">
            Your order information is secure and encrypted
          </p>
        </div>
      </div>
    </div>

    <!-- Item Count Summary -->
    <div class="mt-4 text-center">
      <p class="text-sm text-gray-500">
        {{ totalItemCount }} {{ totalItemCount === 1 ? 'item' : 'items' }} in your cart
      </p>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, computed } from 'vue'

interface CartItem {
  name: string
  sku?: string
  price: number
  quantity: number
  image?: string
}

interface CartTotals {
  subtotal: number
  tax: number
  taxRate: number
  total: number
}

export default defineComponent({
  name: 'OrderSummary',
  props: {
    cart: {
      type: Object as () => Record<string, CartItem>,
      required: true
    },
    cartTotals: {
      type: Object as () => CartTotals,
      required: true
    },
    isProcessing: {
      type: Boolean,
      default: false
    }
  },
  emits: ['submit'],
  setup(props) {
    const totalItemCount = computed(() => {
      return Object.values(props.cart).reduce((total, item) => total + item.quantity, 0)
    })

    const formatPrice = (price: number): string => {
      return parseFloat(price.toString()).toFixed(2)
    }

    return {
      totalItemCount,
      formatPrice
    }
  }
})
</script>
```
