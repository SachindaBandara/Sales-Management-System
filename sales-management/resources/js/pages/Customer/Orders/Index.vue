Index:

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Pagination, PaginationContent, PaginationFirst, PaginationItem, PaginationLast, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import axios from 'axios';

// Define interfaces based on OrderController data structure
interface OrderItem {
  product_id: number;
  product_name: string;
  product_sku: string;
  product_price: number;
  quantity: number;
  total_price: number;
  product_snapshot: {
    name: string;
    sku: string;
    price: number;
    image: string;
    description: string;
  };
}

interface Order {
  id: number;
  order_number: string;
  user_id: number;
  status: string;
  subtotal: number;
  tax_amount: number;
  shipping_amount: number;
  discount_amount: number;
  total_amount: number;
  payment_method: string;
  payment_status: string;
  billing_address: {
    first_name: string;
    last_name: string;
    email: string;
    phone: string;
    address_line_1: string;
    address_line_2?: string;
    city: string;
    state: string;
    postal_code: string;
    country: string;
  };
  shipping_address: {
    first_name: string;
    last_name: string;
    email: string;
    phone: string;
    address_line_1: string;
    address_line_2?: string;
    city: string;
    state: string;
    postal_code: string;
    country: string;
  };
  notes?: string;
  created_at: string;
  items: OrderItem[];
}

// Define PaginatedOrders interface for pagination
interface PaginatedOrders {
  data: Order[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

const page = usePage();
const orders = ref<PaginatedOrders>({ data: [], current_page: 1, last_page: 1, per_page: 10, total: 0 });
const loading = ref(true);

// Fetch orders from the backend or use Inertia props
const fetchOrders = async (pageNum: number = 1) => {
  try {
    loading.value = true;
    if (pageNum === 1 && page.props.orders) {
      orders.value = page.props.orders as PaginatedOrders;
    } else {
      const response = await axios.get(`/customer/orders?page=${pageNum}`);
      orders.value = response.data as PaginatedOrders;
    }
  } catch (error) {
    console.error('Failed to fetch orders:', error);
    orders.value = { data: [], current_page: 1, last_page: 1, per_page: 10, total: 0 };
  } finally {
    loading.value = false;
  }
};

// Navigate to order details
const viewOrder = (order: Order) => {
  try {
    router.get(route('customer.orders.show', { order: order.id }));
  } catch (error) {
    console.error('Failed to navigate to order details:', error);
    window.alert('Failed to view order details');
  }
};

// Cancel an order
const cancelOrder = async (order: Order) => {
  if (!window.confirm(`Are you sure you want to cancel order ${order.order_number}?`)) {
    return;
  }

  try {
    const response = await axios.post(`/customer/orders/${order.id}/cancel`);
    window.alert(response.data.success || 'Order cancelled successfully');
    await fetchOrders(orders.value.current_page); // Refresh orders
  } catch (error: any) {
    console.error('Failed to cancel order:', error);
    window.alert(error.response?.data?.error || 'Failed to cancel order');
  }
};

// Format currency
const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount);
};

// Format date for display
const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

// Map status to badge variant
const getStatusVariant = (status: string) => {
  switch (status.toLowerCase()) {
    case 'pending':
      return 'secondary';
    case 'cancelled':
      return 'destructive';
    case 'completed':
      return 'default';
    default:
      return 'outline';
  }
};

// Compute items count for each order
const getItemsCount = (items: OrderItem[] | undefined) => {
  return items ? items.reduce((total, item) => total + item.quantity, 0) : 0;
};

// Compute page numbers for pagination
const pageNumbers = computed(() => {
  const maxPagesToShow = 5;
  const currentPage = orders.value.current_page || 1;
  const lastPage = orders.value.last_page || 1;
  const startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
  const endPage = Math.min(lastPage, startPage + maxPagesToShow - 1);
  return Array.from({ length: endPage - startPage + 1 }, (_, i) => startPage + i);
});

// Handle pagination page updates
const handlePageUpdate = (newPage: number) => {
  if (newPage >= 1 && newPage <= (orders.value.last_page || 1)) {
    fetchOrders(newPage);
  }
};

onMounted(() => {
  fetchOrders();
});
</script>

<template>
  <AuthenticatedLayout>
    <div class="container mx-auto py-6">
      <Card>
        <CardHeader>
          <CardTitle>My Orders</CardTitle>
        </CardHeader>
        <CardContent>
          <div v-if="loading" class="text-center py-4">Loading...</div>
          <div v-else-if="!orders.data?.length" class="text-center py-4">No orders found.</div>
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
              <TableRow v-for="order in orders.data" :key="order.id">
                <TableCell>{{ order.order_number }}</TableCell>
                <TableCell>{{ formatDate(order.created_at) }}</TableCell>
                <TableCell>{{ getItemsCount(order.items) }}</TableCell>
                <TableCell>{{ formatCurrency(order.subtotal) }}</TableCell>
                <TableCell>{{ formatCurrency(order.tax_amount) }}</TableCell>
                <TableCell>{{ formatCurrency(order.total_amount) }}</TableCell>
                <TableCell>
                  <Badge :variant="getStatusVariant(order.status)">{{ order.status }}</Badge>
                </TableCell>
                <TableCell>
                  <Badge :variant="getStatusVariant(order.payment_status)">{{ order.payment_status }}</Badge>
                </TableCell>
                <TableCell class="space-x-2">
                  <Button variant="outline" @click="viewOrder(order)">View Details</Button>
                  <Button variant="destructive" @click="cancelOrder(order)">Cancel</Button>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>

          <Pagination
            v-if="orders.last_page > 1"
            :total="orders.total || 0"
            :itemsPerPage="orders.per_page || 10"
            :page="orders.current_page || 1"
            class="mt-4"
            @update:page="handlePageUpdate"
          >
            <PaginationContent>
              <PaginationFirst
                :disabled="orders.current_page === 1"
                @click="handlePageUpdate(1)"
              />
              <PaginationPrevious
                :disabled="orders.current_page === 1"
                @click="handlePageUpdate((orders.current_page || 1) - 1)"
              />
              <PaginationItem
                v-for="page in pageNumbers"
                :key="page"
                :value="page"
                :isActive="page === orders.current_page"
              >
                <Button
                  :variant="page === orders.current_page ? 'default' : 'outline'"
                  @click="handlePageUpdate(page)"
                >
                  {{ page }}
                </Button>
              </PaginationItem>
              <PaginationNext
                :disabled="orders.current_page === orders.last_page"
                @click="handlePageUpdate((orders.current_page || 1) + 1)"
              />
              <PaginationLast
                :disabled="orders.current_page === orders.last_page"
                @click="handlePageUpdate(orders.last_page || 1)"
              />
            </PaginationContent>
          </Pagination>
        </CardContent>
      </Card>
    </div>
  </AuthenticatedLayout>
</template>
