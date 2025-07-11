<template>
  <Card>
    <CardHeader>
      <CardTitle>Order Status</CardTitle>
    </CardHeader>
    <CardContent class="space-y-4">
      <div class="flex justify-between items-center">
        <span class="text-sm font-medium">Order Status:</span>
        <Badge :variant="getStatusBadgeVariant(status)">
          {{ status.charAt(0).toUpperCase() + status.slice(1) }}
        </Badge>
      </div>
      <div class="flex justify-between items-center">
        <span class="text-sm font-medium">Payment Status:</span>
        <Badge :variant="getPaymentStatusBadgeVariant(paymentStatus)">
          {{ paymentStatus.charAt(0).toUpperCase() + paymentStatus.slice(1) }}
        </Badge>
      </div>
      <div class="flex justify-between items-center">
        <span class="text-sm font-medium">Payment Method:</span>
        <span class="text-sm">{{ formatPaymentMethod(paymentMethod) }}</span>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

interface Props {
  status: string;
  paymentStatus: string;
  paymentMethod: string;
}

defineProps<Props>();

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
</script>
