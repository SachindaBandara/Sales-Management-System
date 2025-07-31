<template>
  <Dialog v-model:open="isOpen" @update:open="onUpdateOpen">
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>{{ title }}</DialogTitle>
        <DialogDescription>{{ description }}</DialogDescription>
      </DialogHeader>
      <div class="grid gap-4 py-4">
        <slot />
      </div>
      <DialogFooter>
        <Button variant="outline" @click="close">Cancel</Button>
        <Button :variant="confirmVariant" @click="confirm">{{ confirmText }}</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

interface Props {
  title?: string;
  description?: string;
  confirmText?: string;
  confirmVariant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link';
}
withDefaults(defineProps<Props>(), {
  title: 'Confirm Action',
  description: 'Are you sure you want to proceed with this action?',
  confirmText: 'Confirm',
  confirmVariant: 'default',
});

const isOpen = ref(false);
const emit = defineEmits<{
  (e: 'confirm'): void;
  (e: 'close'): void;
}>();

const open = () => {
  isOpen.value = true;
};

const close = () => {
  isOpen.value = false;
  emit('close');
};

const confirm = () => {
  emit('confirm');
  close();
};

const onUpdateOpen = (value: boolean) => {
  isOpen.value = value;
  if (!value) {
    emit('close');
  }
};

defineExpose({ open, close });
</script>
