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
          <TableCell>
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                  <MoreHorizontal class="h-4 w-4" />
                  <span class="sr-only">Open menu</span>
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end" class="w-48">
                <DropdownMenuItem @click="handleViewOrder(order)">
                  <Eye class="mr-2 h-4 w-4" />
                  View Details
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem
                  @click="handleInvoiceDownload(order.id)"
                  :disabled="downloadingInvoices.has(order.id)"
                >
                  <FileDown class="mr-2 h-4 w-4" />
                  Download Invoice
                </DropdownMenuItem>
                <DropdownMenuItem @click="handleInvoicePreview(order.id)">
                  <FileText class="mr-2 h-4 w-4" />
                  Preview Invoice
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem
                  @click="handleCancelOrder(order)"
                  :disabled="order.status === 'cancelled' || order.status === 'completed'"
                  class="text-destructive focus:text-destructive"
                >
                  <XCircle class="mr-2 h-4 w-4" />
                  Cancel Order
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Eye, FileDown, FileText, MoreHorizontal, XCircle } from 'lucide-vue-next';
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

defineProps<Props>();
const emit = defineEmits<Emits>();

// Track downloading invoices
const downloadingInvoices = ref(new Set<number>());

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

// Handle invoice download
const handleInvoiceDownload = async (orderId: number): Promise<void> => {
  if (downloadingInvoices.value.has(orderId)) return;

  try {
    downloadingInvoices.value.add(orderId);

    const downloadUrl = `/customer/orders/${orderId}/invoice/download`;

    // Create a temporary link element and trigger download
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.target = '_blank';
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    window.alert('Invoice download started');
  } catch (error) {
    console.error('Invoice download failed:', error);
    window.alert('Failed to download invoice. Please try again.');
  } finally {
    setTimeout(() => {
      downloadingInvoices.value.delete(orderId);
    }, 2000);
  }
};

// Handle invoice preview
const handleInvoicePreview = (orderId: number): void => {
  try {
    const previewUrl = `/customer/orders/${orderId}/invoice/preview`;
    window.open(previewUrl, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');
    window.alert('Invoice preview opened');
  } catch (error) {
    console.error('Invoice preview failed:', error);
    window.alert('Failed to preview invoice. Please try again.');
  }
};
</script>
