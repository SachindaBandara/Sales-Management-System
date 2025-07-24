<template>
  <Button 
    @click="handleClick"
    :disabled="disabled || isLoading"
    variant="default"
    class="flex items-center space-x-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-300 transition-colors duration-200"
  >
    <Loader2 v-if="isLoading" class="h-4 w-4 animate-spin" />
    <Plus v-else class="h-4 w-4" />
    <span>{{ isLoading ? loadingLabel : label }}</span>
  </Button>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Loader2, Plus } from 'lucide-vue-next';

interface Props {
  disabled?: boolean;
  isLoading?: boolean;
  label?: string;
  loadingLabel?: string;
  href?: string;
}

interface Emits {
  (e: 'click'): void;
}

const props = withDefaults(defineProps<Props>(), {
  disabled: false,
  isLoading: false,
  label: 'Add New',
  loadingLabel: 'Adding...',
  href: undefined
});

const emit = defineEmits<Emits>();

const handleClick = () => {
  if (!props.disabled && !props.isLoading) {
    emit('click');
  }
};
</script>