<!-- src/components/orders/OrderItemsCard.vue -->
<template>
  <Card>
    <CardHeader>
      <CardTitle>Order Items ({{ items.length }} items)</CardTitle>
    </CardHeader>
    <CardContent>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Product</TableHead>
            <TableHead>Quantity</TableHead>
            <TableHead>Price</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="item in items" :key="item.id">
            <TableCell>
              <div class="flex items-center gap-3">
                <img
                  v-if="item.product.image"
                  :src="item.product.image"
                  :alt="item.product.name"
                  class="w-12 h-12 object-cover rounded"
                />
                <div v-else class="w-12 h-12 bg-gray-100 rounded flex items-center justify-center">
                  <Package class="w-6 h-6 text-gray-400" />
                </div>
                <div>
                  <div class="font-medium">{{ item.product.name }}</div>
                  <div class="text-sm text-gray-500">SKU: {{ item.product.sku }}</div>
                </div>
              </div>
            </TableCell>
            <TableCell>{{ item.quantity }}</TableCell>
            <TableCell>${{ formatCurrency(item.product_price) }}</TableCell>
          </TableRow>
        </TableBody>
      </Table>

      <!-- Order Summary -->
      <div class="mt-6 border-t pt-4">
        <div class="flex justify-end">
          <div class="w-64 space-y-2">
            <div class="flex justify-between">
              <span>Subtotal:</span>
              <span>${{ formatCurrency(subtotal) }}</span>
            </div>
            <div class="flex justify-between">
              <span>Tax:</span>
              <span>${{ formatCurrency(taxAmount) }}</span>
            </div>
            <div class="flex justify-between">
              <span>Shipping:</span>
              <span>${{ formatCurrency(shippingAmount || 0) }}</span>
            </div>
            <div class="flex justify-between font-bold text-lg border-t pt-2">
              <span>Total:</span>
              <span>${{ formatCurrency(totalAmount) }}</span>
            </div>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Package } from 'lucide-vue-next';
import { formatCurrency } from '@/lib/formatters';
import { OrderItem } from '@/types/order';

interface Props {
  items: OrderItem[];
  subtotal: number;
  taxAmount: number;
  shippingAmount?: number;
  totalAmount: number;
}

defineProps<Props>();
</script>
