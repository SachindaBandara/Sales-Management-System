<template>
  <AuthenticatedLayout>
    <div class="container mx-auto py-6 px-4">
      <div class="mb-6">
        <h1 class="text-2xl font-bold">Order #{{ order.order_number }}</h1>
        <p class="text-gray-600 mt-1">Placed on {{ formatDate(order.created_at) }}</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Items -->
        <div class="lg:col-span-2">
          <OrderItemsTable :items="order.items" />
        </div>

        <!-- Order Summary Sidebar -->
        <div class="space-y-6">
          <OrderStatusCard
            :status="order.status"
            :payment-status="order.payment_status"
            :payment-method="order.payment_method"
          />

          <OrderSummaryCard
            :subtotal="order.subtotal"
            :tax-amount="order.tax_amount"
            :shipping-amount="order.shipping_amount"
            :discount-amount="order.discount_amount"
            :total-amount="order.total_amount"
          />

          <OrderAddressCard
            :billing-address="order.billing_address"
            :shipping-address="order.shipping_address"
          />

          <OrderNotesCard :notes="order.notes" />

          <OrderActionsCard
            :can-cancel="canCancelOrder()"
            @cancel="cancelOrder"
          />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import OrderItemsTable from '@/components/Order/Show/OrderItemsTable.vue';
import OrderStatusCard from '@/components/Order/Show/OrderStatusCard.vue';
import OrderSummaryCard from '@/components/Order/Show/OrderSummaryCard.vue';
import OrderAddressCard from '@/components/Order/Show/OrderAddressCard.vue';
import OrderNotesCard from '@/components/Order/Show/OrderNotesCard.vue';
import OrderActionsCard from '@/components/Order/Show/OrderActionsCard.vue';
import type { Order } from '@/types/order';

interface Props {
  order: Order;
}

const props = defineProps<Props>();

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const canCancelOrder = () => {
  return props.order.status.toLowerCase() === 'pending';
};

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
