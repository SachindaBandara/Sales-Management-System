<template>
  <Card>
    <CardHeader>
      <CardTitle>Order Status</CardTitle>
    </CardHeader>
    <CardContent>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <Label>Order Status</Label>
          <div class="mt-2">
            <Badge :variant="getStatusVariant(orderStatus)">
              {{ formatStatusLabel(orderStatus) }}
            </Badge>
          </div>
        </div>
        <div>
          <Label>Payment Status</Label>
          <div class="mt-2">
            <Badge :variant="getPaymentStatusVariant(paymentStatus)">
              {{ formatStatusLabel(paymentStatus) }}
            </Badge>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { getStatusVariant, getPaymentStatusVariant, formatStatusLabel } from '@/lib/formatters';

interface Props {
  orderStatus: string;
  paymentStatus: string;
  orderId: number;
}

defineProps<Props>();
</script>
