<script setup lang="ts">
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { ScrollArea } from '@/components/ui/scroll-area'
import { ShoppingCart, X, Trash2 } from 'lucide-vue-next'
import { useCart } from '@/composables/useCart'

const { cartItems, cartTotals, cartCount, removeFromCart, isEmpty } = useCart()

const isOpen = ref(false)

const toggleCart = () => {
  isOpen.value = !isOpen.value
}

const closeCart = () => {
  isOpen.value = false
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
      class="absolute right-0 top-full mt-2 w-80 z-50"
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

          <!-- Cart Items -->
          <div v-else>
            <ScrollArea class="h-64">
              <div class="space-y-2 p-4">
                <div
                  v-for="(item, id) in cartItems"
                  :key="id"
                  class="flex items-center gap-3 p-2 rounded-md hover:bg-gray-50"
                >
                  <img
                    :src="item.image || '/images/placeholder.png'"
                    :alt="item.name"
                    class="w-12 h-12 object-cover rounded"
                  />
                  <div class="flex-1 min-w-0">
                    <h4 class="font-medium text-sm truncate">{{ item.name }}</h4>
                    <p class="text-xs text-gray-500">
                      {{ item.quantity }} × LKR {{ item.price.toLocaleString() }}
                    </p>
                  </div>
                  <Button
                    variant="ghost"
                    size="sm"
                    @click="removeFromCart(id)"
                    class="text-red-600 hover:text-red-700"
                  >
                    <Trash2 class="h-4 w-4" />
                  </Button>
                </div>
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
                  View Cart
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