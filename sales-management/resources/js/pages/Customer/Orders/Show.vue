Show:

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import axios from 'axios';

// Define the OrderItem interface
interface OrderItem {
  product_name: string;
  product_sku: string;
  product_price: number;
  quantity: number;
  total_price: number;
  formatted_price: string;
  formatted_total: string;
  product_snapshot: {
    name: string;
    sku: string;
    price: number;
    image: string;
    description: string;
  };
}

// Define the Order interface
interface Order {
  id: number;
  order_number: string;
  status: string;
  status_badge: 'default' | 'destructive' | 'outline' | 'secondary' | null | undefined;
  payment_status: string;
  payment_status_badge: 'default' | 'destructive' | 'outline' | 'secondary' | null | undefined;
  total_amount: number;
  formatted_total: string;
  items_count: number;
  created_at: string;
  billing_address: Record<string, string>;
  shipping_address: Record<string, string>;
  payment_method: string;
  notes: string | null;
  items: OrderItem[];
}

const route = useRoute();
const order = ref<Order | null>(null);
const loading = ref(true);

// Fetch order details from the backend
const fetchOrder = async () => {
  try {
    loading.value = true;
    const response = await axios.get(`/customer/orders/${route.params.order}`);
    order.value = response.data.order;
  } catch (error) {
    console.error('Failed to fetch order:', error);
  } finally {
    loading.value = false;
  }
};

// Cancel the order
const cancelOrder = async () => {
  try {
    await axios.post(`/customer/orders/${route.params.order}/cancel`);
    if (order.value) {
      order.value.status = 'cancelled';
      order.value.status_badge = 'destructive';
    }
  } catch (error) {
    console.error('Failed to cancel order:', error);
  }
};

// Format date for display
const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

// Format address for display
const formatAddress = (address: Record<string, string>) => {
  return [
    `${address.first_name} ${address.last_name}`,
    address.address_line_1,
    address.address_line_2 || '',
    `${address.city}, ${address.state} ${address.postal_code}`,
    address.country,
  ].filter(line => line).join('\n');
};

onMounted(() => {
  fetchOrder();
});
</script>

<template>
  <AuthenticatedLayout>
    <div class="container mx-auto py-6">
      <Card v-if="loading">
        <CardContent class="text-center py-4">Loading...</CardContent>
      </Card>
      <Card v-else-if="order">
        <CardHeader>
          <CardTitle>Order #{{ order.order_number }}</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h3 class="text-lg font-semibold mb-2">Order Details</h3>
              <p><strong>Date:</strong> {{ formatDate(order.created_at) }}</p>
              <p><strong>Status:</strong> <Badge :variant="order.status_badge">{{ order.status }}</Badge></p>
              <p><strong>Payment Status:</strong> <Badge :variant="order.payment_status_badge">{{ order.payment_status }}</Badge></p>
              <p><strong>Total:</strong> {{ order.formatted_total }}</p>
              <p><strong>Payment Method:</strong> {{ order.payment_method }}</p>
              <p v-if="order.notes"><strong>Notes:</strong> {{ order.notes }}</p>
            </div>
            <div>
              <h3 class="text-lg font-semibold mb-2">Addresses</h3>
              <div class="mb-4">
                <strong>Billing Address:</strong>
                <p class="whitespace-pre-line">{{ formatAddress(order.billing_address) }}</p>
              </div>
              <div>
                <strong>Shipping Address:</strong>
                <p class="whitespace-pre-line">{{ formatAddress(order.shipping_address) }}</p>
              </div>
            </div>
          </div>

          <h3 class="text-lg font-semibold mt-6 mb-2">Order Items</h3>
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
              <TableRow v-for="item in order.items" :key="item.product_sku">
                <TableCell>
                  <div class="flex items-center gap-2">
                    <img
                      :src="item.product_snapshot.image"
                      :alt="item.product_name"
                      class="w-12 h-12 object-cover rounded"
                    />
                    <span>{{ item.product_name }}</span>
                  </div>
                </TableCell>
                <TableCell>{{ item.product_sku }}</TableCell>
                <TableCell>{{ item.formatted_price }}</TableCell>
                <TableCell>{{ item.quantity }}</TableCell>
                <TableCell>{{ item.formatted_total }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>

          <AlertDialog v-if="order.status === 'pending'">
            <AlertDialogTrigger as-child>
              <Button variant="destructive" class="mt-4">Cancel Order</Button>
            </AlertDialogTrigger>
            <AlertDialogContent>
              <AlertDialogHeader>
                <AlertDialogTitle>Cancel Order</AlertDialogTitle>
                <AlertDialogDescription>
                  Are you sure you want to cancel this order? This action cannot be undone.
                </AlertDialogDescription>
              </AlertDialogHeader>
              <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction @click="cancelOrder">Confirm</AlertDialogAction>
              </AlertDialogFooter>
            </AlertDialogContent>
          </AlertDialog>
        </CardContent>
      </Card>
      <Card v-else>
        <CardContent class="text-center py-4">Order not found.</CardContent>
      </Card>
    </div>
  </AuthenticatedLayout>
</template>
