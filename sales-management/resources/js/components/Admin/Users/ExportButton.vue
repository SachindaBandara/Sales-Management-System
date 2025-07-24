<template>
  <Button 
    @click="handleExport"
    :disabled="disabled || isExporting"
    variant="default"
    class="flex items-center space-x-2 bg-green-600 hover:bg-green-700 disabled:bg-green-300 transition-colors duration-200"
  >
    <Loader2 v-if="isExporting" class="h-4 w-4 animate-spin" />
    <Download v-else class="h-4 w-4" />
    <span>{{ isExporting ? exportingLabel : label }}</span>
  </Button>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Loader2, Download } from 'lucide-vue-next';

interface Props {
  disabled?: boolean;
  isExporting?: boolean;
  label?: string;
  exportingLabel?: string;
}

interface Emits {
  (e: 'click'): void;
}

const props = withDefaults(defineProps<Props>(), {
  disabled: false,
  isExporting: false,
  label: 'Export Excel',
  exportingLabel: 'Exporting...'
});

const emit = defineEmits<Emits>();

const handleExport = () => {
  if (!props.disabled && !props.isExporting) {
    emit('click');
  }
};
</script>