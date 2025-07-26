<template>
  <div class="w-full">
    <!-- Table Container -->
    <div class="rounded-md border">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead class="font-medium">Order Number</TableHead>
            <TableHead class="font-medium">Customer</TableHead>
            <TableHead class="font-medium">Status</TableHead>
            <TableHead class="font-medium">Payment</TableHead>
            <TableHead class="font-medium">Items</TableHead>
            <TableHead class="font-medium">Total</TableHead>
            <TableHead class="font-medium">Date</TableHead>
            <TableHead class="font-medium">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="order in sortedOrders" :key="order.id" class="transition-colors hover:bg-muted/50">
            <!-- Order Number Column -->
            <TableCell class="py-4">
              <div class="text-sm font-medium text-foreground">
                {{ order.order_number }}
              </div>
            </TableCell>

            <!-- Customer Column -->
            <TableCell class="py-4">
              <div class="flex items-center space-x-3">
                <Avatar class="h-10 w-10">
                  <img v-if="order.user.avatar" :src="order.user.avatar" class="h-10 w-10 rounded-full object-cover" alt="Customer avatar" />
                  <AvatarFallback class="bg-blue-100 font-semibold text-blue-600">
                    {{ order.user.name.charAt(0).toUpperCase() }}
                  </AvatarFallback>
                </Avatar>
                <div class="min-w-0 flex-1">
                  <div class="truncate text-sm font-medium text-foreground">
                    {{ order.user.name }}
                  </div>
                  <p class="truncate text-xs text-muted-foreground">
                    {{ order.user.email }}
                  </p>
                </div>
              </div>
            </TableCell>

            <!-- Status Column -->
            <TableCell>
              <div class="flex items-center space-x-2">
                <div :class="getStatusIndicatorClass(order.status)" class="h-2 w-2 rounded-full"></div>
                <Badge :variant="getStatusVariant(order.status)">
                  {{ formatStatus(order.status) }}
                </Badge>
              </div>
            </TableCell>

            <!-- Payment Status Column -->
            <TableCell>
              <div class="flex items-center space-x-2">
                <div :class="getPaymentStatusIndicatorClass(order.payment_status)" class="h-2 w-2 rounded-full"></div>
                <Badge :variant="getPaymentStatusVariant(order.payment_status)">
                  {{ formatStatus(order.payment_status) }}
                </Badge>
              </div>
            </TableCell>

            <!-- Items Column -->
            <TableCell class="text-sm text-muted-foreground">
              {{ order.items_count }}
            </TableCell>

            <!-- Total Column -->
            <TableCell class="text-sm font-medium text-foreground">
              ${{ formatCurrency(order.total_amount) }}
            </TableCell>

            <!-- Date Column -->
            <TableCell class="text-muted-foreground">
              <div class="flex items-center space-x-2">
                <Calendar class="h-4 w-4" />
                <span class="text-sm">{{ formatDate(order.created_at) }}</span>
              </div>
            </TableCell>

            <!-- Actions Column -->
            <TableCell>
              <DropdownMenu>
                <DropdownMenuTrigger asChild>
                  <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                    <MoreHorizontal class="h-4 w-4" />
                    <span class="sr-only">Open menu</span>
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-48">
                  <DropdownMenuItem @click="$emit('view-order', order.id)">
                    <Eye class="mr-2 h-4 w-4" />
                    View Order
                  </DropdownMenuItem>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem @click="$emit('update-order-status', order.id, 'processing')">
                    <Package class="mr-2 h-4 w-4" />
                    Mark as Processing
                  </DropdownMenuItem>
                  <DropdownMenuItem @click="$emit('update-order-status', order.id, 'shipped')">
                    <Truck class="mr-2 h-4 w-4" />
                    Mark as Shipped
                  </DropdownMenuItem>
                  <DropdownMenuItem @click="$emit('update-order-status', order.id, 'delivered')">
                    <CheckCircle class="mr-2 h-4 w-4" />
                    Mark as Delivered
                  </DropdownMenuItem>
                  <DropdownMenuItem @click="$emit('update-order-status', order.id, 'cancelled')">
                    <XCircle class="mr-2 h-4 w-4" />
                    Cancel Order
                  </DropdownMenuItem>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem @click="$emit('update-payment-status', order.id, 'paid')">
                    <CreditCard class="mr-2 h-4 w-4" />
                    Mark as Paid
                  </DropdownMenuItem>
                  <DropdownMenuItem @click="$emit('update-payment-status', order.id, 'refunded')">
                    <ArrowLeftCircle class="mr-2 h-4 w-4" />
                    Mark as Refunded
                  </DropdownMenuItem>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem @click="$emit('delete-order', order.id)" class="text-destructive focus:text-destructive">
                    <Trash2 class="mr-2 h-4 w-4" />
                    Delete Order
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            </TableCell>
          </TableRow>

          <!-- Empty State -->
          <TableRow v-if="sortedOrders.length === 0">
            <TableCell :colspan="8" class="h-32 text-center">
              <div class="flex flex-col items-center justify-center space-y-3">
                <Box class="h-12 w-12 text-muted-foreground/50" />
                <div class="space-y-1">
                  <p class="text-sm font-medium text-muted-foreground">No orders found</p>
                  <p class="text-xs text-muted-foreground">Try adjusting your search criteria or create new orders.</p>
                </div>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Box, Calendar, CheckCircle, CreditCard, ArrowLeftCircle, Eye, MoreHorizontal, Package, Trash2, Truck, XCircle } from 'lucide-vue-next';
import { Order } from '@/types/order';

interface Props {
  orders: Order[];
}

interface Emits {
  (e: 'view-order', id: number): void;
  (e: 'update-order-status', id: number, status: string): void;
  (e: 'update-payment-status', id: number, payment_status: string): void;
  (e: 'delete-order', id: number): void;
}

const props = defineProps<Props>();
defineEmits<Emits>();

const sortedOrders = computed(() => {
  return [...props.orders].sort((a, b) => a.order_number.localeCompare(b.order_number));
});

const getStatusVariant = (status: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
  const variants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    pending: 'outline',
    processing: 'secondary',
    shipped: 'secondary',
    delivered: 'default',
    cancelled: 'destructive'
  };
  return variants[status.toLowerCase()] || 'outline';
};

const getPaymentStatusVariant = (status: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
  const variants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    pending: 'outline',
    paid: 'default',
    failed: 'destructive',
    refunded: 'secondary'
  };
  return variants[status.toLowerCase()] || 'outline';
};

const getStatusIndicatorClass = (status: string): string => {
  const classes: Record<string, string> = {
    pending: 'bg-yellow-500',
    processing: 'bg-blue-500',
    shipped: 'bg-purple-500',
    delivered: 'bg-green-500',
    cancelled: 'bg-red-500'
  };
  return classes[status.toLowerCase()] || 'bg-gray-500';
};

const getPaymentStatusIndicatorClass = (status: string): string => {
  const classes: Record<string, string> = {
    pending: 'bg-orange-500',
    paid: 'bg-green-500',
    failed: 'bg-red-500',
    refunded: 'bg-gray-500'
  };
  return classes[status.toLowerCase()] || 'bg-gray-500';
};

const formatStatus = (status: string): string => {
  return status.charAt(0).toUpperCase() + status.slice(1).toLowerCase();
};

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount);
};

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit'
  });
};
</script>

<style scoped>
/* Ensure consistent styling with BrandsTable.vue */
.transition-colors {
  transition: all 0.2s ease-in-out;
}
</style>