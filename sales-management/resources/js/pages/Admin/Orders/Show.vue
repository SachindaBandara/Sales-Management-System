<!-- src/views/orders/OrderDetails.vue -->
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

      <Toaster />

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Details -->
        <div class="lg:col-span-2 space-y-6">
          <OrderStatusCard
            :order-status="order.status"
            :payment-status="order.payment_status"
            :order-id="order.id"
            @update:orderStatus="order.status = $event"
            @update:paymentStatus="order.payment_status = $event"
          />
          <OrderItemsCard
            :items="order.items"
            :subtotal="order.subtotal"
            :tax-amount="order.tax_amount"
            :shipping-amount="order.shipping_amount"
            :total-amount="order.total_amount"
          />
          <OrderNotesCard
            :order-id="order.id"
            :notes="order.notes"
            @update:notes="order.notes = $event"
          />
        </div>

        <!-- Order Info Sidebar -->
        <div class="space-y-6">
          <CustomerInfoCard :user="order.user" />
          <AddressCard
            title="Shipping Address"
            :address="order.shipping_address"
            :user-name="order.user.name"
          />
          <AddressCard
            title="Billing Address"
            :address="order.billing_address"
            :user-name="order.user.name"
          />
          <OrderTimelineCard
            :created-at="order.created_at"
            :processed-at="order.processed_at"
            :shipped-at="order.shipped_at"
            :delivered-at="order.delivered_at"
          />
          <OrderActionsCard :order-id="order.id" />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Toaster } from '@/components/ui/sonner';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { ArrowLeft, Printer } from 'lucide-vue-next';
import OrderStatusCard from '@/components/Admin/Orders/OrderStatusCard.vue';
import OrderItemsCard from '@/components/Admin/Orders/OrderItemsCard.vue';
import OrderNotesCard from '@/components/Admin/Orders/OrderNotesCard.vue';
import CustomerInfoCard from '@/components/Admin/Orders/CustomerInfoCard.vue';
import AddressCard from '@/components/Admin/Orders/AddressCard.vue';
import OrderTimelineCard from '@/components/Admin/Orders/OrderTimelineCard.vue';
import OrderActionsCard from '@/components/Admin/Orders/OrderActionsCard.vue';
import { Order } from '@/types/order';

interface Props {
  order: Order;
}

const props = defineProps<Props>();
const order = ref<Order>(props.order);

const goBack = () => {
  router.get(route('admin.orders.index'));
};

const printOrder = () => {
  window.print();
};
</script>

<style scoped>
@media print {
  .no-print {
    display: none !important;
  }
}
</style>
