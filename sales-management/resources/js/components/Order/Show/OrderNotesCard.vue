<template>
  <Card v-if="notes">
    <CardHeader>
      <CardTitle>Notes</CardTitle>
    </CardHeader>
    <CardContent>
      <p class="text-sm text-gray-600">{{ notes }}</p>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface Props {
  notes?: string;
}

defineProps<Props>();
</script>
