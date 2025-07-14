<template>
    <AuthenticatedLayout>
  <div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
      <div class="flex items-center gap-4">
        <Button @click="goBack" variant="outline" size="sm">
          <ArrowLeft class="w-4 h-4 mr-2" />
          Back to Orders
        </Button>
        <h1 class="text-3xl font-bold text-gray-900">Order #{{ order.order_number }}</h1>
      </div>
      <div class="flex gap-2">
        <Button @click="printOrder" variant="outline" size="sm">
          <Printer class="w-4 h-4 mr-2" />
          Print
        </Button>
      </div>
    </div>

    <!-- Toaster for notifications -->
    <Toaster />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Order Details -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Order Status -->
        <Card>
          <CardHeader>
            <CardTitle>Order Status</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <Label>Order Status</Label>
                <Select v-model="orderStatus" @update:modelValue="handleStatusUpdate">
                  <SelectTrigger>
                    <SelectValue :placeholder="orderStatus" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="pending">Pending</SelectItem>
                    <SelectItem value="processing">Processing</SelectItem>
                    <SelectItem value="shipped">Shipped</SelectItem>
                    <SelectItem value="delivered">Delivered</SelectItem>
                    <SelectItem value="cancelled">Cancelled</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div>
                <Label>Payment Status</Label>
                <Select v-model="paymentStatus" @update:modelValue="handlePaymentStatusUpdate">
                  <SelectTrigger>
                    <SelectValue :placeholder="paymentStatus" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="pending">Pending</SelectItem>
                    <SelectItem value="paid">Paid</SelectItem>
                    <SelectItem value="failed">Failed</SelectItem>
                    <SelectItem value="refunded">Refunded</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
            <div class="mt-4 flex gap-2">
              <Badge :variant="getStatusVariant(order.status)">
                {{ formatStatusLabel(order.status) }}
              </Badge>
              <Badge :variant="getPaymentStatusVariant(order.payment_status)">
                {{ formatStatusLabel(order.payment_status) }}
              </Badge>
            </div>
          </CardContent>
        </Card>

        <!-- Order Items -->
        <Card>
          <CardHeader>
            <CardTitle>Order Items ({{ order.items.length }} items)</CardTitle>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Product</TableHead>
                  <TableHead>Quantity</TableHead>
                  <TableHead>Price</TableHead>
                  <TableHead>Total</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="item in order.items" :key="item.id">
                  <TableCell>
                    <div class="flex items-center gap-3">
                      <img
                        v-if="item.product.image"
                        :src="item.product.image"
                        :alt="item.product.name"
                        class="w-12 h-12 object-cover rounded"
                      />
                      <div class="w-12 h-12 bg-gray-100 rounded flex items-center justify-center" v-else>
                        <Package class="w-6 h-6 text-gray-400" />
                      </div>
                      <div>
                        <div class="font-medium">{{ item.product.name }}</div>
                        <div class="text-sm text-gray-500">SKU: {{ item.product.sku }}</div>
                      </div>
                    </div>
                  </TableCell>
                  <TableCell>{{ item.quantity }}</TableCell>
                  <TableCell>${{ formatCurrency(item.price) }}</TableCell>
                  <TableCell class="font-medium">${{ formatCurrency(item.total) }}</TableCell>
                </TableRow>
              </TableBody>
            </Table>

            <!-- Order Summary -->
            <div class="mt-6 border-t pt-4">
              <div class="flex justify-end">
                <div class="w-64 space-y-2">
                  <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span>${{ formatCurrency(order.subtotal) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Tax:</span>
                    <span>${{ formatCurrency(order.tax_amount) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Shipping:</span>
                    <span>${{ formatCurrency(order.shipping_amount || 0) }}</span>
                  </div>
                  <div class="flex justify-between font-bold text-lg border-t pt-2">
                    <span>Total:</span>
                    <span>${{ formatCurrency(order.total_amount) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Notes -->
        <Card>
          <CardHeader>
            <CardTitle>Order Notes</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <Textarea
                v-model="orderNotes"
                placeholder="Add notes about this order..."
                rows="4"
              />
              <Button @click="updateNotes" size="sm" :disabled="isUpdatingNotes">
                <Save class="w-4 h-4 mr-2" />
                {{ isUpdatingNotes ? 'Saving...' : 'Save Notes' }}
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Order Info Sidebar -->
      <div class="space-y-6">
        <!-- Customer Information -->
        <Card>
          <CardHeader>
            <CardTitle>Customer Information</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-3">
              <div>
                <Label class="text-sm font-medium text-gray-500">Name</Label>
                <p class="font-medium">{{ order.user.name }}</p>
              </div>
              <div>
                <Label class="text-sm font-medium text-gray-500">Email</Label>
                <p class="text-blue-600">{{ order.user.email }}</p>
              </div>
              <div>
                <Label class="text-sm font-medium text-gray-500">Phone</Label>
                <p>{{ order.user.phone || 'Not provided' }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Shipping Address -->
        <Card>
          <CardHeader>
            <CardTitle>Shipping Address</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-1">
              <p class="font-medium">{{ order.shipping_address?.name || order.user.name }}</p>
              <p>{{ order.shipping_address?.address_line_1 || 'Not provided' }}</p>
              <p v-if="order.shipping_address?.address_line_2">
                {{ order.shipping_address.address_line_2 }}
              </p>
              <p v-if="order.shipping_address?.city">
                {{ order.shipping_address.city }}, {{ order.shipping_address.state }}
                {{ order.shipping_address.postal_code }}
              </p>
              <p v-if="order.shipping_address?.country">{{ order.shipping_address.country }}</p>
            </div>
          </CardContent>
        </Card>

        <!-- Billing Address -->
        <Card>
          <CardHeader>
            <CardTitle>Billing Address</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-1">
              <p class="font-medium">{{ order.billing_address?.name || order.user.name }}</p>
              <p>{{ order.billing_address?.address_line_1 || 'Same as shipping' }}</p>
              <p v-if="order.billing_address?.address_line_2">
                {{ order.billing_address.address_line_2 }}
              </p>
              <p v-if="order.billing_address?.city">
                {{ order.billing_address.city }}, {{ order.billing_address.state }}
                {{ order.billing_address.postal_code }}
              </p>
              <p v-if="order.billing_address?.country">{{ order.billing_address.country }}</p>
            </div>
          </CardContent>
        </Card>

        <!-- Order Timeline -->
        <Card>
          <CardHeader>
            <CardTitle>Order Timeline</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div class="flex items-center gap-3">
                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                <div>
                  <p class="font-medium">Order Placed</p>
                  <p class="text-sm text-gray-500">{{ formatDate(order.created_at) }}</p>
                </div>
              </div>
              <div v-if="order.processed_at" class="flex items-center gap-3">
                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                <div>
                  <p class="font-medium">Order Processed</p>
                  <p class="text-sm text-gray-500">{{ formatDate(order.processed_at) }}</p>
                </div>
              </div>
              <div v-if="order.shipped_at" class="flex items-center gap-3">
                <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                <div>
                  <p class="font-medium">Order Shipped</p>
                  <p class="text-sm text-gray-500">{{ formatDate(order.shipped_at) }}</p>
                </div>
              </div>
              <div v-if="order.delivered_at" class="flex items-center gap-3">
                <div class="w-2 h-2 bg-green-600 rounded-full"></div>
                <div>
                  <p class="font-medium">Order Delivered</p>
                  <p class="text-sm text-gray-500">{{ formatDate(order.delivered_at) }}</p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Actions -->
        <Card>
          <CardHeader>
            <CardTitle>Actions</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-2">
              <Button @click="sendOrderEmail" variant="outline" class="w-full">
                <Mail class="w-4 h-4 mr-2" />
                Send Order Email
              </Button>
              <Button @click="generateInvoice" variant="outline" class="w-full">
                <FileText class="w-4 h-4 mr-2" />
                Generate Invoice
              </Button>
              <Button @click="deleteOrder" variant="destructive" class="w-full" :disabled="isDeletingOrder">
                <Trash2 class="w-4 h-4 mr-2" />
                {{ isDeletingOrder ? 'Deleting...' : 'Delete Order' }}
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </div>
  </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { ref} from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Textarea } from '@/components/ui/textarea';
import { toast } from 'sonner';
import { Toaster } from '@/components/ui/sonner';
import AuthenticatedLayout from '../../../layouts/AuthenticatedLayout.vue';
import {
  ArrowLeft,
  Printer,
  Save,
  Mail,
  FileText,
  Trash2,
  Package
} from 'lucide-vue-next';

// Type definitions
interface Address {
  name?: string;
  address_line_1?: string;
  address_line_2?: string;
  city?: string;
  state?: string;
  postal_code?: string;
  country?: string;
}

interface User {
  id: number;
  name: string;
  email: string;
  phone?: string;
}

interface Product {
  id: number;
  name: string;
  sku: string;
  image?: string;
}

interface OrderItem {
  id: number;
  product: Product;
  quantity: number;
  price: number;
  total: number;
}

interface Order {
  id: number;
  order_number: string;
  user: User;
  status: 'pending' | 'processing' | 'shipped' | 'delivered' | 'cancelled';
  payment_status: 'pending' | 'paid' | 'failed' | 'refunded';
  subtotal: number;
  tax_amount: number;
  shipping_amount?: number;
  total_amount: number;
  items: OrderItem[];
  shipping_address?: Address;
  billing_address?: Address;
  notes?: string;
  created_at: string;
  processed_at?: string;
  shipped_at?: string;
  delivered_at?: string;
}

interface Props {
  order: Order;
}

const props = defineProps<Props>();

// Reactive state
const order = ref<Order>(props.order);
const orderStatus = ref<string>(props.order.status);
const paymentStatus = ref<string>(props.order.payment_status);
const orderNotes = ref<string>(props.order.notes || '');
const isUpdatingNotes = ref<boolean>(false);
const isDeletingOrder = ref<boolean>(false);

// Navigation
const goBack = () => {
  router.get(route('admin.orders.index'));
};

// Status update handlers
const handleStatusUpdate = async (newStatus: string) => {
  if (!newStatus || newStatus === orderStatus.value) return;

  try {
    const response = await fetch(route('admin.orders.updateStatus', order.value.id), {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({ status: newStatus })
    });

    const data = await response.json();

    if (data.success) {
      order.value.status = newStatus as Order['status'];
      orderStatus.value = newStatus;
      toast.success(data.message || 'Order status updated successfully');
    } else {
      orderStatus.value = order.value.status;
      toast.error(data.message || 'Failed to update order status');
    }
  } catch (error) {
    console.error('Error updating order status:', error);
    orderStatus.value = order.value.status;
    toast.error('Failed to update order status');
  }
};

const handlePaymentStatusUpdate = async (newStatus: string) => {
  if (!newStatus || newStatus === paymentStatus.value) return;

  try {
    const response = await fetch(route('admin.orders.updatePaymentStatus', order.value.id), {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({ payment_status: newStatus })
    });

    const data = await response.json();

    if (data.success) {
      order.value.payment_status = newStatus as Order['payment_status'];
      paymentStatus.value = newStatus;
      toast.success(data.message || 'Payment status updated successfully');
    } else {
      paymentStatus.value = order.value.payment_status;
      toast.error(data.message || 'Failed to update payment status');
    }
  } catch (error) {
    console.error('Error updating payment status:', error);
    paymentStatus.value = order.value.payment_status;
    toast.error('Failed to update payment status');
  }
};

// Notes update
const updateNotes = async () => {
  if (isUpdatingNotes.value) return;

  isUpdatingNotes.value = true;

  try {
    const response = await fetch(route('admin.orders.addNotes', order.value.id), {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({ notes: orderNotes.value })
    });

    const data = await response.json();

    if (data.success) {
      order.value.notes = orderNotes.value;
      toast.success(data.message || 'Notes updated successfully');
    } else {
      toast.error(data.message || 'Failed to update notes');
    }
  } catch (error) {
    console.error('Error updating notes:', error);
    toast.error('Failed to update notes');
  } finally {
    isUpdatingNotes.value = false;
  }
};

// Order actions
const deleteOrder = async () => {
  if (isDeletingOrder.value) return;

  if (!confirm('Are you sure you want to delete this order? This action cannot be undone.')) {
    return;
  }

  isDeletingOrder.value = true;

  try {
    const response = await fetch(route('admin.orders.destroy', order.value.id), {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    });

    const data = await response.json();

    if (data.success) {
      toast.success(data.message || 'Order deleted successfully');
      router.get(route('admin.orders.index'));
    } else {
      toast.error(data.message || 'Failed to delete order');
    }
  } catch (error) {
    console.error('Error deleting order:', error);
    toast.error('Failed to delete order');
  } finally {
    isDeletingOrder.value = false;
  }
};

const printOrder = () => {
  window.print();
};

const sendOrderEmail = () => {
  toast.info('Order email functionality will be available soon');
};

const generateInvoice = () => {
  toast.info('Invoice generation functionality will be available soon');
};

// Utility functions
const getStatusVariant = (status: string) => {
  const variants = {
    pending: 'secondary',
    processing: 'default',
    shipped: 'default',
    delivered: 'default',
    cancelled: 'destructive'
  } as const;
  return variants[status as keyof typeof variants] || 'outline';
};

const getPaymentStatusVariant = (status: string) => {
  const variants = {
    pending: 'secondary',
    paid: 'default',
    failed: 'destructive',
    refunded: 'outline'
  } as const;
  return variants[status as keyof typeof variants] || 'outline';
};

const formatStatusLabel = (status: string): string => {
  return status.charAt(0).toUpperCase() + status.slice(1);
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
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>

<style scoped>
@media print {
  .no-print {
    display: none !important;
  }
}
</style>