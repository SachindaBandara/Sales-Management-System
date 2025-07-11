<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import OrdersTable from '@/components/Order/OrdersTable.vue';
import OrderPagination from '@/components/Order/OrderPagination.vue';
import axios from 'axios';
import type { Order, PaginatedOrders } from '@/types/order';

const page = usePage();
const orders = ref<PaginatedOrders>({
  data: [],
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0
});
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
const handleViewOrder = (order: Order) => {
  try {
    router.get(route('customer.orders.show', { order: order.id }));
  } catch (error) {
    console.error('Failed to navigate to order details:', error);
    window.alert('Failed to view order details');
  }
};

// Cancel an order
const handleCancelOrder = async (order: Order) => {
  if (!window.confirm(`Are you sure you want to cancel order ${order.order_number}?`)) {
    return;
  }

  try {
    const response = await axios.post(`/customer/orders/${order.id}/cancel`);
    window.alert(response.data.success || 'Order cancelled successfully');
    await fetchOrders(orders.value.current_page);
  } catch (error: any) {
    console.error('Failed to cancel order:', error);
    window.alert(error.response?.data?.error || 'Failed to cancel order');
  }
};

// Handle pagination page updates
const handlePageChange = (newPage: number) => {
  fetchOrders(newPage);
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
          <OrdersTable
            :orders="orders.data"
            :loading="loading"
            @view-order="handleViewOrder"
            @cancel-order="handleCancelOrder"
          />

          <OrderPagination
            :current-page="orders.current_page"
            :last-page="orders.last_page"
            :per-page="orders.per_page"
            :total="orders.total"
            @page-change="handlePageChange"
          />
        </CardContent>
      </Card>
    </div>
  </AuthenticatedLayout>
</template>
