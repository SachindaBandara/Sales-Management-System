<template>
  <Card>
    <CardContent class="p-0">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Order Number</TableHead>
            <TableHead>Customer</TableHead>
            <TableHead>Status</TableHead>
            <TableHead>Payment</TableHead>
            <TableHead>Items</TableHead>
            <TableHead>Total</TableHead>
            <TableHead>Date</TableHead>
            <TableHead>Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="order in orders" :key="order.id">
            <TableCell class="font-medium">{{ order.order_number }}</TableCell>
            <TableCell>
              <div>
                <div class="font-medium">{{ order.user.name }}</div>
                <div class="text-sm text-gray-500">{{ order.user.email }}</div>
              </div>
            </TableCell>
            <TableCell>
              <Badge :variant="getStatusVariant(order.status)">
                {{ order.status }}
              </Badge>
            </TableCell>
            <TableCell>
              <Badge :variant="getPaymentStatusVariant(order.payment_status)">
                {{ order.payment_status }}
              </Badge>
            </TableCell>
            <TableCell>{{ order.items_count }}</TableCell>
            <TableCell class="font-medium">${{ formatCurrency(order.total_amount) }}</TableCell>
            <TableCell>{{ formatDate(order.created_at) }}</TableCell>
            <TableCell>
              <div class="flex gap-2">
                <Button
                  @click="$emit('view-order', order.id)"
                  variant="outline"
                  size="sm"
                >
                  <Eye class="w-4 h-4" />
                </Button>
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button variant="outline" size="sm">
                      <MoreHorizontal class="w-4 h-4" />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent>
                    <DropdownMenuItem @click="$emit('update-order-status', order.id, 'processing')">
                      Mark as Processing
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="$emit('update-order-status', order.id, 'shipped')">
                      Mark as Shipped
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="$emit('update-order-status', order.id, 'delivered')">
                      Mark as Delivered
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="$emit('update-order-status', order.id, 'cancelled')">
                      Cancel Order
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem @click="$emit('update-payment-status', order.id, 'paid')">
                      Mark as Paid
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="$emit('update-payment-status', order.id, 'refunded')">
                      Mark as Refunded
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem @click="$emit('delete-order', order.id)" class="text-red-600">
                      Delete Order
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Eye, MoreHorizontal } from 'lucide-vue-next'
import { Order } from '@/types/order'

defineProps<{
  orders: Order[]
}>()

defineEmits<{
  (e: 'view-order', id: number): void
  (e: 'update-order-status', id: number, status: string): void
  (e: 'update-payment-status', id: number, payment_status: string): void
  (e: 'delete-order', id: number): void
}>()

const getStatusVariant = (status: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
  const variants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    pending: 'secondary',
    processing: 'default',
    shipped: 'default',
    delivered: 'default',
    cancelled: 'destructive'
  }
  return variants[status] || 'outline'
}

const getPaymentStatusVariant = (status: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
  const variants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    pending: 'secondary',
    paid: 'default',
    failed: 'destructive',
    refunded: 'outline'
  }
  return variants[status] || 'outline'
}

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount)
}

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}
</script>