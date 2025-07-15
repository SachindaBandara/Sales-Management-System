<!-- src/components/orders/CustomerInfoCard.vue -->
<template>
  <Card>
    <CardHeader>
      <CardTitle>Customer Information</CardTitle>
    </CardHeader>
    <CardContent>
      <div class="space-y-3">
        <div>
          <Label class="text-sm font-medium text-gray-500">Name</Label>
          <p class="font-medium">{{ user.name }}</p>
        </div>
        <div>
          <Label class="text-sm font-medium text-gray-500">Email</Label>
          <p class="text-blue-600">{{ user.email }}</p>
        </div>
        <div>
          <Label class="text-sm font-medium text-gray-500">Phone</Label>
          <p>{{ user.phone || 'Not provided' }}</p>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { User } from '@/types/order';

interface Props {
  user: User;
}

defineProps<Props>();
</script>
