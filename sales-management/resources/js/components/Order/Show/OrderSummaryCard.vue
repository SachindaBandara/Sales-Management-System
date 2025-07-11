<template>
  <Card>
    <CardHeader>
      <CardTitle>Order Summary</CardTitle>
    </CardHeader>
    <CardContent class="space-y-3">
      <div class="flex justify-between">
        <span>Subtotal:</span>
        <span>{{ formatCurrency(subtotal) }}</span>
      </div>
      <div class="flex justify-between">
        <span>Tax:</span>
        <span>{{ formatCurrency(taxAmount) }}</span>
      </div>
      <div class="flex justify-between" v-if="shippingAmount > 0">
        <span>Shipping:</span>
        <span>{{ formatCurrency(shippingAmount) }}</span>
      </div>
      <div class="flex justify-between" v-if="discountAmount > 0">
        <span>Discount:</span>
        <span class="text-green-600">-{{ formatCurrency(discountAmount) }}</span>
      </div>
      <div class="border-t pt-3">
        <div class="flex justify-between font-semibold text-lg">
          <span>Total:</span>
          <span>{{ formatCurrency(totalAmount) }}</span>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface Props {
  subtotal: number;
  taxAmount: number;
  shippingAmount: number;
  discountAmount: number;
  totalAmount: number;
}

defineProps<Props>();

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'LKR',
  }).format(amount);
};
</script>
