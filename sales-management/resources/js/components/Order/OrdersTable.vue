<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import OrderStatusBadge from '@/components/Order/OrderStatusBadge.vue';
import type { Order, OrderItem } from '../../types/order';

interface Props {
  orders: Order[];
  loading?: boolean;
}

interface Emits {
  (e: 'view-order', order: Order): void;
  (e: 'cancel-order', order: Order): void;
}

// Remove unused props variable and use defineProps directly
defineProps<Props>();

const emit = defineEmits<Emits>();

// Format currency
const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'LKR',
  }).format(amount);
};

// Format date for display
const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

// Compute items count for each order with proper typing
const getItemsCount = (items: OrderItem[] | undefined): number => {
  return items ? items.reduce((total: number, item: OrderItem) => total + item.quantity, 0) : 0;
};

const handleViewOrder = (order: Order): void => {
  emit('view-order', order);
};

const handleCancelOrder = (order: Order): void => {
  emit('cancel-order', order);
};
</script>

<template>
  <div>
    <div v-if="loading" class="text-center py-4">Loading...</div>
    <div v-else-if="!orders?.length" class="text-center py-4">No orders found.</div>
    <Table v-else>
      <TableHeader>
        <TableRow>
          <TableHead>Order Number</TableHead>
          <TableHead>Date</TableHead>
          <TableHead>Items</TableHead>
          <TableHead>Subtotal</TableHead>
          <TableHead>Tax</TableHead>
          <TableHead>Total</TableHead>
          <TableHead>Status</TableHead>
          <TableHead>Payment</TableHead>
          <TableHead>Actions</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow v-for="order in orders" :key="order.id">
          <TableCell>{{ order.order_number }}</TableCell>
          <TableCell>{{ formatDate(order.created_at) }}</TableCell>
          <TableCell>{{ getItemsCount(order.items) }}</TableCell>
          <TableCell>{{ formatCurrency(order.subtotal) }}</TableCell>
          <TableCell>{{ formatCurrency(order.tax_amount) }}</TableCell>
          <TableCell>{{ formatCurrency(order.total_amount) }}</TableCell>
          <TableCell>
            <OrderStatusBadge :status="order.status" />
          </TableCell>
          <TableCell>
            <OrderStatusBadge :status="order.payment_status" />
          </TableCell>
          <TableCell class="space-x-2">
            <Button variant="outline" @click="handleViewOrder(order)">
              View Details
            </Button>
            <Button
              variant="destructive"
              @click="handleCancelOrder(order)"
              :disabled="order.status === 'cancelled' || order.status === 'completed'"
            >
              Cancel
            </Button>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>
