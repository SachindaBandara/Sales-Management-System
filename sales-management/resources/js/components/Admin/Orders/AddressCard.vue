<!-- src/components/orders/AddressCard.vue -->
<template>
  <Card>
    <CardHeader>
      <CardTitle>{{ title }}</CardTitle>
    </CardHeader>
    <CardContent>
      <div class="space-y-1">
        <p class="font-medium">{{ address?.name || userName }}</p>
        <p>{{ address?.address_line_1 || 'Not provided' }}</p>
        <p v-if="address?.address_line_2">{{ address.address_line_2 }}</p>
        <p v-if="address?.city">
          {{ address.city }}, {{ address.state }} {{ address.postal_code }}
        </p>
        <p v-if="address?.country">{{ address.country }}</p>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Address } from '@/types/order';

interface Props {
  title: string;
  address?: Address;
  userName: string;
}

defineProps<Props>();
</script>
