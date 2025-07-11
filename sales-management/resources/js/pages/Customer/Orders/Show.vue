<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle, AlertDialogTrigger } from '@/components/ui/alert-dialog';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

// Define the OrderItem interface
interface OrderItem {
  id: number;
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

// Define the User interface
interface User {
  id: number;
  name: string;
  email: string;
}

// Define the Order interface
interface Order {
  id: number;
  order_number: string;
  status: string;
  payment_status: string;
  payment_method: string;
  total_amount: number;
  subtotal: number;
  tax_amount: number;
  shipping_amount: number;
  discount_amount: number;
  created_at: string;
  updated_at: string;
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
  items: OrderItem[];
  user: User;
}

// Define props interface
interface Props {
  order: Order;
}

// Define props
const props = defineProps<Props>();

// Get status badge variant
const getStatusBadgeVariant = (status: string) => {
  switch (status.toLowerCase()) {
    case 'pending':
      return 'outline';
    case 'processing':
      return 'default';
    case 'shipped':
      return 'default';
    case 'delivered':
      return 'default';
    case 'cancelled':
      return 'destructive';
    default:
      return 'secondary';
  }
};

// Get payment status badge variant
const getPaymentStatusBadgeVariant = (status: string) => {
  switch (status.toLowerCase()) {
    case 'pending':
      return 'outline';
    case 'paid':
      return 'default';
    case 'failed':
      return 'destructive';
    case 'refunded':
      return 'secondary';
    default:
      return 'secondary';
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
const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

// Format address for display
const formatAddress = (address: Order['billing_address']) => {
  const lines = [
    `${address.first_name} ${address.last_name}`,
    address.address_line_1,
    address.address_line_2,
    `${address.city}, ${address.state} ${address.postal_code}`,
    address.country,
  ];

  return lines.filter(line => line && line.trim()).join('\n');
};

// Format payment method for display
const formatPaymentMethod = (method: string) => {
  switch (method.toLowerCase()) {
    case 'cash_on_delivery':
      return 'Cash on Delivery';
    case 'card':
      return 'Credit/Debit Card';
    case 'paypal':
      return 'PayPal';
    default:
      return method.charAt(0).toUpperCase() + method.slice(1);
  }
};

// Check if order can be cancelled
const canCancelOrder = () => {
  return props.order.status.toLowerCase() === 'pending';
};

// Handle image error
const handleImageError = (event: Event) => {
  const target = event.target as HTMLImageElement;
  if (target) {
    target.src = '/images/placeholder.png';
  }
};

// Cancel the order
const cancelOrder = () => {
  router.post(route('customer.orders.cancel', props.order.id), {}, {
    onSuccess: () => {
      // The page will be reloaded with updated data
    },
    onError: (errors) => {
      console.error('Failed to cancel order:', errors);
    },
  });
};
</script>

<template>
  <AuthenticatedLayout>
    <div class="container mx-auto py-6 px-4">
      <div class="mb-6">
        <h1 class="text-2xl font-bold">Order #{{ order.order_number }}</h1>
        <p class="text-gray-600 mt-1">Placed on {{ formatDate(order.created_at) }}</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Details -->
        <div class="lg:col-span-2">
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
                  <TableRow v-for="item in order.items" :key="item.id">
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
        </div>

        <!-- Order Summary -->
        <div class="space-y-6">
          <!-- Order Status -->
          <Card>
            <CardHeader>
              <CardTitle>Order Status</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="flex justify-between items-center">
                <span class="text-sm font-medium">Order Status:</span>
                <Badge :variant="getStatusBadgeVariant(order.status)">
                  {{ order.status.charAt(0).toUpperCase() + order.status.slice(1) }}
                </Badge>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm font-medium">Payment Status:</span>
                <Badge :variant="getPaymentStatusBadgeVariant(order.payment_status)">
                  {{ order.payment_status.charAt(0).toUpperCase() + order.payment_status.slice(1) }}
                </Badge>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm font-medium">Payment Method:</span>
                <span class="text-sm">{{ formatPaymentMethod(order.payment_method) }}</span>
              </div>
            </CardContent>
          </Card>

          <!-- Order Summary -->
          <Card>
            <CardHeader>
              <CardTitle>Order Summary</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div class="flex justify-between">
                <span>Subtotal:</span>
                <span>{{ formatCurrency(order.subtotal) }}</span>
              </div>
              <div class="flex justify-between">
                <span>Tax:</span>
                <span>{{ formatCurrency(order.tax_amount) }}</span>
              </div>
              <div class="flex justify-between" v-if="order.shipping_amount > 0">
                <span>Shipping:</span>
                <span>{{ formatCurrency(order.shipping_amount) }}</span>
              </div>
              <div class="flex justify-between" v-if="order.discount_amount > 0">
                <span>Discount:</span>
                <span class="text-green-600">-{{ formatCurrency(order.discount_amount) }}</span>
              </div>
              <div class="border-t pt-3">
                <div class="flex justify-between font-semibold text-lg">
                  <span>Total:</span>
                  <span>{{ formatCurrency(order.total_amount) }}</span>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Addresses -->
          <Card>
            <CardHeader>
              <CardTitle>Addresses</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div>
                <h4 class="font-medium mb-2">Billing Address</h4>
                <p class="text-sm text-gray-600 whitespace-pre-line">
                  {{ formatAddress(order.billing_address) }}
                </p>
              </div>
              <div>
                <h4 class="font-medium mb-2">Shipping Address</h4>
                <p class="text-sm text-gray-600 whitespace-pre-line">
                  {{ formatAddress(order.shipping_address) }}
                </p>
              </div>
            </CardContent>
          </Card>

          <!-- Notes -->
          <Card v-if="order.notes">
            <CardHeader>
              <CardTitle>Notes</CardTitle>
            </CardHeader>
            <CardContent>
              <p class="text-sm text-gray-600">{{ order.notes }}</p>
            </CardContent>
          </Card>

          <!-- Actions -->
          <Card v-if="canCancelOrder()">
            <CardContent class="pt-6">
              <AlertDialog>
                <AlertDialogTrigger as-child>
                  <Button variant="destructive" class="w-full">Cancel Order</Button>
                </AlertDialogTrigger>
                <AlertDialogContent>
                  <AlertDialogHeader>
                    <AlertDialogTitle>Cancel Order</AlertDialogTitle>
                    <AlertDialogDescription>
                      Are you sure you want to cancel this order? This action cannot be undone and your items will be returned to stock.
                    </AlertDialogDescription>
                  </AlertDialogHeader>
                  <AlertDialogFooter>
                    <AlertDialogCancel>No, Keep Order</AlertDialogCancel>
                    <AlertDialogAction @click="cancelOrder" class="bg-red-600 hover:bg-red-700">
                      Yes, Cancel Order
                    </AlertDialogAction>
                  </AlertDialogFooter>
                </AlertDialogContent>
              </AlertDialog>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
