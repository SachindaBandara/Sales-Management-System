<template>
  <Dialog :open="open" @update:open="handleOpenChange">
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle class="text-lg font-semibold text-gray-900">
          {{ title }}
        </DialogTitle>
        <DialogDescription class="text-sm text-gray-600">
          {{ message }}
        </DialogDescription>
      </DialogHeader>
      <div class="flex justify-end space-x-3 mt-4">
        <Button
          variant="outline"
          @click="emit('cancel')"
          :disabled="isConfirming"
        >
          {{ cancelText }}
        </Button>
        <Button
          :variant="confirmVariant"
          @click="emit('confirm')"
          :disabled="isConfirming"
          class="flex items-center space-x-2"
        >
          <Loader2 v-if="isConfirming" class="h-4 w-4 animate-spin" />
          <span>{{ confirmText }}</span>
        </Button>
      </div>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Loader2 } from 'lucide-vue-next';

interface Props {
  open: boolean;
  title?: string;
  message?: string;
  confirmText?: string;
  cancelText?: string;
  confirmVariant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link' | null | undefined;
  isConfirming?: boolean;
}

interface Emits {
  (e: 'update:open', value: boolean): void;
  (e: 'confirm'): void;
  (e: 'cancel'): void;
}

withDefaults(defineProps<Props>(), {
  title: 'Confirm Action',
  message: 'Are you sure you want to perform this action?',
  confirmText: 'Confirm',
  cancelText: 'Cancel',
  confirmVariant: 'destructive',
  isConfirming: false,
});

const emit = defineEmits<Emits>();

const handleOpenChange = (value: boolean) => {
  emit('update:open', value);
  if (!value) {
    emit('cancel');
  }
};
</script>

<style scoped>
/* Ensure smooth transitions for dialog */
.transition-all {
  transition: all 0.2s ease-in-out;
}
</style>