<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <Card>
      <CardContent class="p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">Total Orders</p>
            <p class="text-2xl font-bold">{{ statistics.total_orders }}</p>
          </div>
          <ShoppingCart class="w-8 h-8 text-blue-500" />
        </div>
      </CardContent>
    </Card>

    <Card>
      <CardContent class="p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">Pending Orders</p>
            <p class="text-2xl font-bold text-orange-600">{{ statistics.pending_orders }}</p>
          </div>
          <Clock class="w-8 h-8 text-orange-500" />
        </div>
      </CardContent>
    </Card>

    <Card>
      <CardContent class="p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">Total Revenue</p>
            <p class="text-2xl font-bold text-green-600">${{ formatCurrency(statistics.total_revenue) }}</p>
          </div>
          <DollarSign class="w-8 h-8 text-green-500" />
        </div>
      </CardContent>
    </Card>

    <Card>
      <CardContent class="p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">Today's Orders</p>
            <p class="text-2xl font-bold">{{ statistics.todays_orders }}</p>
          </div>
          <Calendar class="w-8 h-8 text-purple-500" />
        </div>
      </CardContent>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card'
import { ShoppingCart, Clock, DollarSign, Calendar } from 'lucide-vue-next'
import { Statistics } from '@/types/order'

defineProps<{
  statistics: Statistics
}>()

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount)
}
</script>