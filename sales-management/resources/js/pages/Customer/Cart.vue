<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { ShoppingBag, Trash2 } from 'lucide-vue-next'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import CartItemsList from'../../components/Cart/CartItemsList.vue'
import OrderSummary from '../../components/Cart/OrderSummary.vue'
import EmptyCart from '../../components/Cart/EmptyCart.vue'

// Define cart item interface
interface CartItem {
  name: string
  quantity: number
  price: number
  image: string
  product_id: number
}

interface CartTotals {
  subtotal: number
  tax: number
  total: number
  taxRate: number
}

// Get cart data from Inertia props
const props = defineProps<{
  cart?: Record<string, CartItem> | null
  cartTotals?: CartTotals
  cartCount?: number
  success?: string
}>()

// Reactive state
const cart = ref<Record<string, CartItem>>(props.cart || {})
const cartTotals = ref<CartTotals>(props.cartTotals || {
  subtotal: 0,
  tax: 0,
  total: 0,
  taxRate: 0
})
const loading = ref(false)
const updatingItems = ref<Set<string>>(new Set())

// Show success message if present
onMounted(() => {
  if (props.success) {
    toast.success(props.success, {
      description: 'Action completed successfully',
    })
  }
})

// Computed properties
const isEmpty = computed(() => Object.keys(cart.value).length === 0)
const itemCount = computed(() => Object.keys(cart.value).length)

// Helper function to safely get route
const getRouteUrl = (routeName: string, fallback: string = '/') => {
  try {
    return route(routeName)
  } catch {
    console.warn(`Route '${routeName}' not found, using fallback: ${fallback}`)
    return fallback
  }
}

// Update quantity using Inertia.js
const updateQuantity = async (id: string, newQuantity: number) => {
  if (newQuantity < 1 || newQuantity > 99) return

  updatingItems.value.add(id)

  try {
    router.patch(route('customer.cart.update'), {
      id,
      quantity: newQuantity
    }, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: (page) => {
        if (page.props.cart) {
          cart.value = page.props.cart as Record<string, CartItem>
        }
        if (page.props.cartTotals) {
          cartTotals.value = page.props.cartTotals as CartTotals
        }
        toast.success('Cart updated', {
          description: 'Quantity updated successfully',
        })
      },
      onError: (errors) => {
        console.error('Update error:', errors)
        toast.error('Error', {
          description: 'Failed to update cart. Please try again.',
        })
      },
      onFinish: () => {
        updatingItems.value.delete(id)
      }
    })
  } catch (error) {
    console.error('Update quantity error:', error)
    toast.error('Error', {
      description: 'Network error. Please try again.',
    })
    updatingItems.value.delete(id)
  }
}

// Remove item using Inertia.js
const removeItem = async (id: string) => {
  if (loading.value) return

  loading.value = true

  try {
    router.delete(route('customer.cart.remove'), {
      data: { id },
      preserveState: true,
      preserveScroll: true,
      onSuccess: (page) => {
        if (page.props.cart) {
          cart.value = page.props.cart as Record<string, CartItem>
        }
        if (page.props.cartTotals) {
          cartTotals.value = page.props.cartTotals as CartTotals
        }
        toast.success('Item removed', {
          description: 'Item successfully removed from cart',
        })
      },
      onError: (errors) => {
        console.error('Remove error:', errors)
        toast.error('Error', {
          description: 'Failed to remove item. Please try again.',
        })
      },
      onFinish: () => {
        loading.value = false
      }
    })
  } catch (error) {
    console.error('Remove item error:', error)
    toast.error('Error', {
      description: 'Network error. Please try again.',
    })
    loading.value = false
  }
}

// Clear entire cart using Inertia.js
const clearCart = async () => {
  if (loading.value || isEmpty.value) return

  loading.value = true

  try {
    router.delete(route('customer.cart.clear'), {
      preserveState: true,
      preserveScroll: true,
      onSuccess: (page) => {
        cart.value = {}
        if (page.props.cartTotals) {
          cartTotals.value = page.props.cartTotals as CartTotals
        }
        toast.success('Cart cleared', {
          description: 'All items removed from cart',
        })
      },
      onError: (errors) => {
        console.error('Clear cart error:', errors)
        toast.error('Error', {
          description: 'Failed to clear cart. Please try again.',
        })
      },
      onFinish: () => {
        loading.value = false
      }
    })
  } catch (error) {
    console.error('Clear cart error:', error)
    toast.error('Error', {
      description: 'Network error. Please try again.',
    })
    loading.value = false
  }
}
</script>

<template>
<AuthenticatedLayout>
  <div class="container mx-auto py-8 px-4">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <div class="flex items-center space-x-3">
          <ShoppingBag class="h-8 w-8 text-indigo-600" />
          <h1 class="text-3xl font-bold text-gray-900">Shopping Cart</h1>
          <Badge v-if="!isEmpty" variant="secondary">
            {{ itemCount }} {{ itemCount === 1 ? 'item' : 'items' }}
          </Badge>
        </div>
        <Button
          v-if="!isEmpty"
          variant="outline"
          size="sm"
          :disabled="loading"
          @click="clearCart"
        >
          <Trash2 class="h-4 w-4 mr-2" />
          Clear Cart
        </Button>
      </div>

      <!-- Empty Cart -->
      <EmptyCart v-if="isEmpty" :get-route-url="getRouteUrl" />

      <!-- Cart Content -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Cart Items -->
        <CartItemsList
          :cart="cart"
          :updating-items="updatingItems"
          :loading="loading"
          @update-quantity="updateQuantity"
          @remove-item="removeItem"
        />

        <!-- Order Summary -->
        <OrderSummary
          :cart-totals="cartTotals"
          :get-route-url="getRouteUrl"
        />
      </div>
    </div>
  </div>
</AuthenticatedLayout>
</template>
