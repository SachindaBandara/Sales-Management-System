<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ShoppingCart } from 'lucide-vue-next'
import { Badge } from '@/components/ui/badge'
import { useCart } from '@/composables/useCart'

const { cartCount, initializeCart } = useCart()

// Initialize cart count when component mounts
onMounted(() => {
  initializeCart()
})

// Show badge only if there are items
const showBadge = computed(() => cartCount.value > 0)
</script>

<template>
  <Link 
    :href="route('customer.cart')"
    class="relative inline-flex items-center p-2 text-gray-600 hover:text-gray-900 transition-colors"
  >
    <ShoppingCart class="h-6 w-6" />
    <Badge
      v-if="showBadge"
      class="absolute -top-2 -right-2 h-5 w-5 flex items-center justify-center p-0 text-xs bg-red-500 text-white border-2 border-white rounded-full"
    >
      {{ cartCount }}
    </Badge>
  </Link>
</template>