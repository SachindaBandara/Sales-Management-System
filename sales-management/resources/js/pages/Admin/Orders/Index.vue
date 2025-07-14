<template>
    <AuthenticatedLayout>
  <div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-900">Orders Management</h1>
      <Button @click="exportOrders" variant="outline" class="gap-2">
        <Download class="w-4 h-4" />
        Export CSV
      </Button>
    </div>

    <!-- Statistics Cards -->
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

    <!-- Filters -->
    <Card class="mb-6">
      <CardContent class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
          <div>
            <Label for="search">Search</Label>
            <Input
              id="search"
              v-model="filters.search"
              placeholder="Order number or customer..."
              @input="applyFilters"
            />
          </div>

          <div>
            <Label for="status">Status</Label>
            <Select v-model="filters.status" @update:modelValue="applyFilters">
              <SelectTrigger>
                <SelectValue placeholder="All statuses" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="">All statuses</SelectItem>
                <SelectItem value="pending">Pending</SelectItem>
                <SelectItem value="processing">Processing</SelectItem>
                <SelectItem value="shipped">Shipped</SelectItem>
                <SelectItem value="delivered">Delivered</SelectItem>
                <SelectItem value="cancelled">Cancelled</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div>
            <Label for="payment_status">Payment Status</Label>
            <Select v-model="filters.payment_status" @update:modelValue="applyFilters">
              <SelectTrigger>
                <SelectValue placeholder="All payment statuses" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="">All payment statuses</SelectItem>
                <SelectItem value="pending">Pending</SelectItem>
                <SelectItem value="paid">Paid</SelectItem>
                <SelectItem value="failed">Failed</SelectItem>
                <SelectItem value="refunded">Refunded</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div>
            <Label for="date_from">Date From</Label>
            <Input
              id="date_from"
              v-model="filters.date_from"
              type="date"
              @change="applyFilters"
            />
          </div>

          <div>
            <Label for="date_to">Date To</Label>
            <Input
              id="date_to"
              v-model="filters.date_to"
              type="date"
              @change="applyFilters"
            />
          </div>
        </div>

        <div class="mt-4 flex gap-2">
          <Button @click="clearFilters" variant="outline" size="sm">
            Clear Filters
          </Button>
        </div>
      </CardContent>
    </Card>

    <!-- Orders Table -->
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
            <TableRow v-for="order in orders.data" :key="order.id">
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
                    @click="viewOrder(order.id)"
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
                      <DropdownMenuItem @click="updateOrderStatus(order.id, 'processing')">
                        Mark as Processing
                      </DropdownMenuItem>
                      <DropdownMenuItem @click="updateOrderStatus(order.id, 'shipped')">
                        Mark as Shipped
                      </DropdownMenuItem>
                      <DropdownMenuItem @click="updateOrderStatus(order.id, 'delivered')">
                        Mark as Delivered
                      </DropdownMenuItem>
                      <DropdownMenuItem @click="updateOrderStatus(order.id, 'cancelled')">
                        Cancel Order
                      </DropdownMenuItem>
                      <DropdownMenuSeparator />
                      <DropdownMenuItem @click="updatePaymentStatus(order.id, 'paid')">
                        Mark as Paid
                      </DropdownMenuItem>
                      <DropdownMenuItem @click="updatePaymentStatus(order.id, 'refunded')">
                        Mark as Refunded
                      </DropdownMenuItem>
                      <DropdownMenuSeparator />
                      <DropdownMenuItem @click="deleteOrder(order.id)" class="text-red-600">
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

    <!-- Pagination -->
    <div class="mt-6 flex justify-between items-center">
      <div class="text-sm text-gray-600">
        Showing {{ orders.from }} to {{ orders.to }} of {{ orders.total }} orders
      </div>
      <div class="flex gap-2">
        <Button
          @click="changePage(orders.current_page - 1)"
          :disabled="!orders.prev_page_url"
          variant="outline"
          size="sm"
        >
          Previous
        </Button>
        <Button
          @click="changePage(orders.current_page + 1)"
          :disabled="!orders.next_page_url"
          variant="outline"
          size="sm"
        >
          Next
        </Button>
      </div>
    </div>

    <!-- Toast -->
    <Toaster />
  </div>
  </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { ref, reactive} from 'vue'
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Toaster } from '@/components/ui/sonner'
import { toast } from 'sonner'
import AuthenticatedLayout from '../../../layouts/AuthenticatedLayout.vue';
import {
  ShoppingCart,
  Clock,
  DollarSign,
  Calendar,
  Eye,
  MoreHorizontal,
  Download
} from 'lucide-vue-next'

interface User {
  id: number
  name: string
  email: string
}

interface Order {
  id: number
  order_number: string
  user: User
  status: string
  payment_status: string
  subtotal: number
  tax_amount: number
  total_amount: number
  items_count: number
  created_at: string
}

interface OrdersPagination {
  data: Order[]
  current_page: number
  from: number
  to: number
  total: number
  prev_page_url: string | null
  next_page_url: string | null
}

interface Statistics {
  total_orders: number
  pending_orders: number
  processing_orders: number
  shipped_orders: number
  delivered_orders: number
  cancelled_orders: number
  todays_orders: number
  this_month_orders: number
  total_revenue: number
  todays_revenue: number
  this_month_revenue: number
}

interface Props {
  orders: OrdersPagination
  statistics: Statistics
  filters: {
    status?: string
    payment_status?: string
    search?: string
    date_from?: string
    date_to?: string
  }
}

const props = defineProps<Props>()

const filters = reactive({
  status: props.filters.status || '',
  payment_status: props.filters.payment_status || '',
  search: props.filters.search || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || ''
})

const orders = ref<OrdersPagination>(props.orders)
const statistics = ref<Statistics>(props.statistics)

const applyFilters = () => {
  router.get(route('admin.orders.index'), filters, {
    preserveState: true,
    replace: true,
    onSuccess: (page) => {
      orders.value = page.props.orders as OrdersPagination
    }
  })
}

const clearFilters = () => {
  (Object.keys(filters) as Array<keyof typeof filters>).forEach(key => {
    filters[key] = ''
  })
  applyFilters()
}

const changePage = (page: number) => {
  router.get(route('admin.orders.index'), { ...filters, page }, {
    preserveState: true,
    replace: true,
    onSuccess: (page) => {
      orders.value = page.props.orders as OrdersPagination
    }
  })
}

const viewOrder = (id: number) => {
  router.get(route('admin.orders.show', id))
}

const updateOrderStatus = async (id: number, status: string) => {
  try {
    await router.put(route('admin.orders.updateStatus', id), { status }, {
      preserveState: true,
      onSuccess: () => {
        toast.success('Order status updated successfully')
        // Refresh the orders list
        applyFilters()
      },
      onError: () => {
        toast.error('Failed to update order status')
      }
    })
  } catch (error) {
    console.error('Error updating order status:', error)
  }
}

const updatePaymentStatus = async (id: number, payment_status: string) => {
  try {
    await router.put(route('admin.orders.updatePaymentStatus', id), { payment_status }, {
      preserveState: true,
      onSuccess: () => {
        toast.success('Payment status updated successfully')
        // Refresh the orders list
        applyFilters()
      },
      onError: () => {
        toast.error('Failed to update payment status')
      }
    })
  } catch (error) {
    console.error('Error updating payment status:', error)
  }
}

const deleteOrder = async (id: number) => {
  if (!confirm('Are you sure you want to delete this order?')) return

  try {
    await router.delete(route('admin.orders.destroy', id), {
      preserveState: true,
      onSuccess: () => {
        toast.success('Order deleted successfully')
        // Refresh the orders list
        applyFilters()
      },
      onError: () => {
        toast.error('Failed to delete order')
      }
    })
  } catch (error) {
    console.error('Error deleting order:', error)
  }
}

const exportOrders = () => {
  const params = new URLSearchParams(filters).toString()
  window.location.href = route('admin.orders.export') + (params ? `?${params}` : '')
}

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
