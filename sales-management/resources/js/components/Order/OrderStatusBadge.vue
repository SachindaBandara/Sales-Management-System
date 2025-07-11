<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';

interface Props {
  status: string;
}

const props = defineProps<Props>();

// Map status to badge variant
const badgeVariant = computed(() => {
  switch (props.status.toLowerCase()) {
    case 'pending':
      return 'secondary';
    case 'cancelled':
      return 'destructive';
    case 'completed':
    case 'paid':
      return 'default';
    case 'processing':
      return 'outline';
    case 'shipped':
      return 'default';
    case 'delivered':
      return 'default';
    case 'failed':
    case 'unpaid':
      return 'destructive';
    default:
      return 'outline';
  }
});

const displayStatus = computed(() => {
  return props.status.charAt(0).toUpperCase() + props.status.slice(1);
});
</script>

<template>
  <Badge :variant="badgeVariant">
    {{ displayStatus }}
  </Badge>
</template>
