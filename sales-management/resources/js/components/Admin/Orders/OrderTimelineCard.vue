<!-- src/components/orders/OrderTimelineCard.vue -->
<template>
  <Card>
    <CardHeader>
      <CardTitle>Order Timeline</CardTitle>
    </CardHeader>
    <CardContent>
      <div class="space-y-4">
        <div class="flex items-center gap-3">
          <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
          <div>
            <p class="font-medium">Order Placed</p>
            <p class="text-sm text-gray-500">{{ formatDate(createdAt) }}</p>
          </div>
        </div>
        <div v-if="processedAt" class="flex items-center gap-3">
          <div class="w-2 h-2 bg-green-500 rounded-full"></div>
          <div>
            <p class="font-medium">Order Processed</p>
            <p class="text-sm text-gray-500">{{ formatDate(processedAt) }}</p>
          </div>
        </div>
        <div v-if="shippedAt" class="flex items-center gap-3">
          <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
          <div>
            <p class="font-medium">Order Shipped</p>
            <p class="text-sm text-gray-500">{{ formatDate(shippedAt) }}</p>
          </div>
        </div>
        <div v-if="deliveredAt" class="flex items-center gap-3">
          <div class="w-2 h-2 bg-green-600 rounded-full"></div>
          <div>
            <p class="font-medium">Order Delivered</p>
            <p class="text-sm text-gray-500">{{ formatDate(deliveredAt) }}</p>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { formatDate } from '@/lib/formatters';

interface Props {
  createdAt: string;
  processedAt?: string;
  shippedAt?: string;
  deliveredAt?: string;
}

defineProps<Props>();
</script>
