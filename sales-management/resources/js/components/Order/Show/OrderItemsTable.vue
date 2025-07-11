<template>
  <Card>
    <CardHeader>
      <CardTitle>Order Items</CardTitle>
    </CardHeader>
    <CardContent>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Product</TableHead>
            <TableHead>SKU</TableHead>
            <TableHead>Price</TableHead>
            <TableHead>Quantity</TableHead>
            <TableHead>Total</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="item in items" :key="item.id">
            <TableCell>
              <div class="flex items-center gap-3">
                <img
                  :src="item.product_snapshot.image"
                  :alt="item.product_name"
                  class="w-16 h-16 object-cover rounded border"
                  @error="handleImageError"
                />
                <div>
                  <p class="font-medium">{{ item.product_name }}</p>
                  <p class="text-sm text-gray-600">{{ item.product_snapshot.description }}</p>
                </div>
              </div>
            </TableCell>
            <TableCell>{{ item.product_sku }}</TableCell>
            <TableCell>{{ formatCurrency(item.product_price) }}</TableCell>
            <TableCell>{{ item.quantity }}</TableCell>
            <TableCell>{{ formatCurrency(item.total_price) }}</TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import type { OrderItem } from '@/types/order';

interface Props {
  items: OrderItem[];
}

defineProps<Props>();

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'LKR',
  }).format(amount);
};

const handleImageError = (event: Event) => {
  const target = event.target as HTMLImageElement;
  if (target) {
    target.src = '/images/placeholder.png';
  }
};
</script>
