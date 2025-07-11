<template>
  <Card>
    <CardHeader>
      <CardTitle>Addresses</CardTitle>
    </CardHeader>
    <CardContent class="space-y-4">
      <div>
        <h4 class="font-medium mb-2">Billing Address</h4>
        <p class="text-sm text-gray-600 whitespace-pre-line">
          {{ formatAddress(billingAddress) }}
        </p>
      </div>
      <div>
        <h4 class="font-medium mb-2">Shipping Address</h4>
        <p class="text-sm text-gray-600 whitespace-pre-line">
          {{ formatAddress(shippingAddress) }}
        </p>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { Address } from '@/types/order';

interface Props {
  billingAddress: Address;
  shippingAddress: Address;
}

defineProps<Props>();

const formatAddress = (address: Address) => {
  const lines = [
    `${address.first_name} ${address.last_name}`,
    address.address_line_1,
    address.address_line_2,
    `${address.city}, ${address.state} ${address.postal_code}`,
    address.country,
  ];

  return lines.filter(line => line && line.trim()).join('\n');
};
</script>
