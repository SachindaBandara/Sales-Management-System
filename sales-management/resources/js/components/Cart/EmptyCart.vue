<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { ShoppingBag } from 'lucide-vue-next'

interface Props {
  getRouteUrl: (routeName: string, fallback?: string) => string
}

defineProps<Props>()
</script>

<template>
  <div class="text-center py-16">
    <ShoppingBag class="h-16 w-16 text-gray-300 mx-auto mb-4" />
    <h2 class="text-2xl font-semibold text-gray-900 mb-2">Your cart is empty</h2>
    <p class="text-gray-600 mb-6">Looks like you haven't added any items to your cart yet.</p>
    <Link
      :href="getRouteUrl('customer.dashboard', '/')"
      class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 transition-colors"
    >
      Continue Shopping
    </Link>
  </div>
</template>
