<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import { Trash2, ShoppingBag, Minus, Plus, AlertCircle } from 'lucide-vue-next'

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
const totalItems = computed(() =>
  Object.values(cart.value).reduce((sum, item) => sum + item.quantity, 0)
)

// Helper function to safely get route - returns fallback if route doesn't exist
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
    // Using Inertia.js for better Laravel integration
    router.patch(route('customer.cart.update'), {
      id,
      quantity: newQuantity
    }, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: (page) => {
        // Update local state with response data
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
        // Update local state
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
        // Update local state
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

// Increment quantity
const incrementQuantity = (id: string) => {
  const currentQuantity = cart.value[id].quantity
  if (currentQuantity < 99) {
    updateQuantity(id, currentQuantity + 1)
  }
}

// Decrement quantity
const decrementQuantity = (id: string) => {
  const currentQuantity = cart.value[id].quantity
  if (currentQuantity > 1) {
    updateQuantity(id, currentQuantity - 1)
  }
}

// Handle quantity input change
const onQuantityChange = (id: string, event: Event) => {
  const target = event.target as HTMLInputElement
  const newQuantity = parseInt(target.value)

  if (newQuantity && newQuantity >= 1 && newQuantity <= 99) {
    updateQuantity(id, newQuantity)
  }
}
</script>

<template>
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
      <div v-if="isEmpty" class="text-center py-16">
        <ShoppingBag class="h-16 w-16 text-gray-300 mx-auto mb-4" />
        <h2 class="text-2xl font-semibold text-gray-900 mb-2">Your cart is empty</h2>
        <p class="text-gray-600 mb-6">Looks like you haven't added any items to your cart yet.</p>
        <Link
          :href="getRouteUrl('customer.home', '/home')"
          class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 transition-colors"
        >
          Continue Shopping
        </Link>
      </div>

      <!-- Cart Content -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Cart Items -->
        <div class="lg:col-span-2">
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center justify-between">
                <span>Cart Items</span>
                <Badge>{{ totalItems }} {{ totalItems === 1 ? 'item' : 'items' }}</Badge>
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div
                v-for="(item, id) in cart"
                :key="id"
                class="flex items-center gap-4 p-4 border rounded-lg hover:shadow-sm transition-shadow"
              >
                <!-- Product Image -->
                <div class="flex-shrink-0">
                  <img
                    :src="item.image || '/images/placeholder.png'"
                    :alt="item.name"
                    class="w-20 h-20 object-cover rounded-md"
                  />
                </div>

                <!-- Product Details -->
                <div class="flex-1 min-w-0">
                  <h3 class="font-semibold text-gray-900 truncate">{{ item.name }}</h3>
                  <p class="text-indigo-600 font-medium">LKR {{ item.price.toLocaleString() }}</p>
                  <p class="text-sm text-gray-500">
                    Subtotal: LKR {{ (item.price * item.quantity).toLocaleString() }}
                  </p>
                </div>

                <!-- Quantity Controls -->
                <div class="flex items-center space-x-2">
                  <Button
                    variant="outline"
                    size="sm"
                    :disabled="item.quantity <= 1 || updatingItems.has(id)"
                    @click="decrementQuantity(id)"
                  >
                    <Minus class="h-4 w-4" />
                  </Button>
                  <Input
                    :value="item.quantity"
                    type="number"
                    min="1"
                    max="99"
                    class="w-20 text-center"
                    :disabled="updatingItems.has(id)"
                    @change="onQuantityChange(id, $event)"
                  />
                  <Button
                    variant="outline"
                    size="sm"
                    :disabled="item.quantity >= 99 || updatingItems.has(id)"
                    @click="incrementQuantity(id)"
                  >
                    <Plus class="h-4 w-4" />
                  </Button>
                </div>

                <!-- Remove Button -->
                <Button
                  variant="ghost"
                  size="sm"
                  :disabled="loading"
                  @click="removeItem(id)"
                  class="text-red-600 hover:text-red-700 hover:bg-red-50"
                >
                  <Trash2 class="h-4 w-4" />
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1">
          <Card class="sticky top-6">
            <CardHeader>
              <CardTitle>Order Summary</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="flex justify-between">
                <span>Subtotal</span>
                <span>LKR {{ cartTotals.subtotal.toLocaleString() }}</span>
              </div>
              <div class="flex justify-between">
                <span>Tax ({{ (cartTotals.taxRate * 100).toFixed(0) }}%)</span>
                <span>LKR {{ cartTotals.tax.toLocaleString() }}</span>
              </div>
              <Separator />
              <div class="flex justify-between font-semibold text-lg">
                <span>Total</span>
                <span>LKR {{ cartTotals.total.toLocaleString() }}</span>
              </div>

              <!-- Shipping Info -->
              <div class="bg-blue-50 p-3 rounded-md">
                <div class="flex items-start space-x-2">
                  <AlertCircle class="h-5 w-5 text-blue-600 mt-0.5" />
                  <div class="text-sm">
                    <p class="font-medium text-blue-900">Free shipping</p>
                    <p class="text-blue-700">On orders over LKR 5,000</p>
                  </div>
                </div>
              </div>
            </CardContent>
            <CardFooter class="flex-col space-y-2">
              <Button class="w-full" size="lg">
                Proceed to Checkout
              </Button>
              <Link
                :href="getRouteUrl('customer.home', '/home')"
                class="w-full inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-indigo-600 hover:text-indigo-700 transition-colors"
              >
                Continue Shopping
              </Link>
            </CardFooter>
          </Card>
        </div>
      </div>
    </div>
  </div>
</template>
