<script setup lang="ts">
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { ScrollArea } from '@/components/ui/scroll-area'
import { ShoppingCart, X, Eye } from 'lucide-vue-next'
import { useCart } from '@/composables/useCart'
import CartItemList from './CartItemsList.vue'

const { cartItems, cartTotals, cartCount, removeFromCart, updateQuantity, isEmpty } = useCart()

const isOpen = ref(false)
const updatingItems = ref(new Set<string>())
const loading = ref(false)

const toggleCart = () => {
  isOpen.value = !isOpen.value
}

const closeCart = () => {
  isOpen.value = false
}

const handleUpdateQuantity = async (id: string, quantity: number) => {
  loading.value = true
  updatingItems.value.add(id)

  try {
    await updateQuantity(id, quantity)
  } finally {
    updatingItems.value.delete(id)
    loading.value = false
  }
}

const handleRemoveItem = async (id: string) => {
  loading.value = true
  updatingItems.value.add(id)

  try {
    await removeFromCart(id)
  } finally {
    updatingItems.value.delete(id)
    loading.value = false
  }
}
</script>

<template>
  <div class="relative">
    <!-- Cart Trigger -->
    <Button
      variant="ghost"
      size="sm"
      class="relative"
      @click="toggleCart"
    >
      <ShoppingCart class="h-5 w-5" />
      <Badge
        v-if="cartCount > 0"
        class="absolute -top-2 -right-2 h-5 w-5 flex items-center justify-center p-0 text-xs bg-red-500 text-white border-2 border-white rounded-full"
      >
        {{ cartCount }}
      </Badge>
    </Button>

    <!-- Mini Cart Dropdown -->
    <div
      v-if="isOpen"
      class="absolute right-0 top-full mt-2 w-96 z-50"
    >
      <Card class="shadow-lg border">
        <CardHeader class="flex flex-row items-center justify-between pb-2">
          <CardTitle class="text-lg">Cart</CardTitle>
          <Button
            variant="ghost"
            size="sm"
            @click="closeCart"
          >
            <X class="h-4 w-4" />
          </Button>
        </CardHeader>

        <CardContent class="p-0">
          <!-- Empty Cart -->
          <div v-if="isEmpty" class="text-center py-8 px-4">
            <ShoppingCart class="h-12 w-12 text-gray-300 mx-auto mb-2" />
            <p class="text-gray-500">Your cart is empty</p>
          </div>

          <!-- Cart Items using CartItemList -->
          <div v-else>
            <ScrollArea class="max-h-80">
              <div class="p-4">
                <CartItemList
                  :cart="cartItems"
                  :updating-items="updatingItems"
                  :loading="loading"
                  @update-quantity="handleUpdateQuantity"
                  @remove-item="handleRemoveItem"
                />
              </div>
            </ScrollArea>

            <!-- Cart Summary -->
            <div class="border-t p-4 space-y-2">
              <div class="flex justify-between text-sm">
                <span>Subtotal</span>
                <span>LKR {{ cartTotals.subtotal.toLocaleString() }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span>Tax</span>
                <span>LKR {{ cartTotals.tax.toLocaleString() }}</span>
              </div>
              <div class="flex justify-between font-semibold border-t pt-2">
                <span>Total</span>
                <span>LKR {{ cartTotals.total.toLocaleString() }}</span>
              </div>

              <div class="space-y-2 pt-2">
                <Link
                  :href="route('customer.cart')"
                  class="w-full inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 transition-colors"
                  @click="closeCart"
                >
                  <Eye class="h-4 w-4 mr-2" />
                  View All Items
                </Link>
                <Button
                  class="w-full"
                  variant="outline"
                  @click="closeCart"
                >
                  Checkout
                </Button>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Overlay -->
    <div
      v-if="isOpen"
      class="fixed inset-0 z-40"
      @click="closeCart"
    ></div>
  </div>
</template>
