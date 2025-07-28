<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Separator } from '@/components/ui/separator'
import { AlertCircle } from 'lucide-vue-next'

interface CartTotals {
  subtotal: number
  tax: number
  total: number
  taxRate: number
}

interface Props {
  cartTotals: CartTotals
  getRouteUrl: (routeName: string, fallback?: string) => string
}

defineProps<Props>()
</script>

<template>
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
        <Link
          :href="getRouteUrl('customer.checkout', '/checkout')"
          class="w-full"
        >
          <Button class="w-full" size="lg">
            Proceed to Checkout
          </Button>
        </Link>
        <Link
          :href="getRouteUrl('customer.home', '/home')"
          class="w-full inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-indigo-600 hover:text-indigo-700 transition-colors"
        >
          Continue Shopping
        </Link>
      </CardFooter>
    </Card>
  </div>
</template>
