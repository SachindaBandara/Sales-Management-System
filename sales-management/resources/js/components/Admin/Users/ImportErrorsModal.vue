<template>
  <Dialog :open="show" @update:open="$emit('close')">
    <DialogContent class="sm:max-w-[550px] max-h-[80vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle class="text-lg font-semibold text-red-900">{{ title }}</DialogTitle>
      </DialogHeader>
      
      <!-- Errors List -->
      <div v-if="errors.length > 0" class="space-y-3 max-h-96 overflow-y-auto">
        <div v-for="error in errors" 
             :key="`error-${error.row}`" 
             class="p-4 bg-red-50 border border-red-200 rounded-lg">
          <div class="font-medium text-red-900 text-sm mb-2">
            {{ rowLabel }} {{ error.row }}
          </div>
          <ul class="text-red-800 text-sm space-y-1">
            <li v-for="errorMsg in error.errors" :key="errorMsg">• {{ errorMsg }}</li>
          </ul>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-8">
        <CheckCircle class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">{{ emptyMessage }}</h3>
      </div>

      <!-- Actions -->
      <DialogFooter>
        <Button
          @click="$emit('close')"
          variant="destructive"
          class="text-sm"
        >
          {{ closeText }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { CheckCircle } from 'lucide-vue-next';

interface ImportError {
  row: number;
  errors: string[];
}

interface Props {
  show: boolean;
  errors: ImportError[];
  title?: string;
  rowLabel?: string;
  emptyMessage?: string;
  closeText?: string;
}

interface Emits {
  (e: 'close'): void;
}

withDefaults(defineProps<Props>(), {
  title: 'Import Errors',
  rowLabel: 'Row',
  emptyMessage: 'No errors found',
  closeText: 'Close'
});

defineEmits<Emits>();
</script>