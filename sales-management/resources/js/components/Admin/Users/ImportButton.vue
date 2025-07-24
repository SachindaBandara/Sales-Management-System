<template>
  <Button 
    @click="handleImport"
    :disabled="disabled"
    variant="default"
    class="flex items-center space-x-2 bg-purple-600 hover:bg-purple-700 disabled:bg-purple-300 transition-colors duration-200"
  >
    <Upload class="h-4 w-4" />
    <span>{{ label }}</span>
  </Button>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Upload } from 'lucide-vue-next';

interface Props {
  disabled?: boolean;
  label?: string;
}

interface Emits {
  (e: 'click'): void;
}

const props = withDefaults(defineProps<Props>(), {
  disabled: false,
  label: 'Import Excel'
});

const emit = defineEmits<Emits>();

const handleImport = () => {
  if (!props.disabled) {
    emit('click');
  }
};
</script>