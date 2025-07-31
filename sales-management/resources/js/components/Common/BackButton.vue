<template>
  <Button
    :variant="variant"
    :size="size"
    class="flex items-center"
    @click="handleClick"
  >
    <ArrowLeft v-if="showIcon" class="w-4 h-4 mr-2" />
    {{ label }}
  </Button>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { ArrowLeft } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

defineProps<{
  to?: string;
  label?: string;
  variant?: 'default' | 'outline' | 'secondary' | 'ghost' | 'link';
  size?: 'default' | 'sm' | 'lg' | 'icon';
  showIcon?: boolean;
  onClick?: () => void;
}>();

const handleClick = (to?: string, onClick?: () => void) => {
  if (onClick) {
    onClick();
    return;
  }

  if (to) {
    try {
      router.visit(route(to), { method: 'get' });
    } catch (error) {
      console.error(`Failed to navigate to route "${to}":`, error);
      window.history.back(); // Fallback to browser back if route fails
    }
  } else {
    window.history.back(); // Fallback to browser back
  }
};
</script>
