<!-- src/components/orders/OrderNotesCard.vue -->
<template>
  <Card>
    <CardHeader>
      <CardTitle>Order Notes</CardTitle>
    </CardHeader>
    <CardContent>
      <div class="space-y-4">
        <Textarea v-model="localNotes" placeholder="Add notes about this order..." rows="4" />
        <Button @click="updateNotes" size="sm" :disabled="isUpdating">
          <Save class="w-4 h-4 mr-2" />
          {{ isUpdating ? 'Saving...' : 'Save Notes' }}
        </Button>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';
import { Button } from '@/components/ui/button';
import { Save } from 'lucide-vue-next';
import { toast } from 'sonner';

interface Props {
  orderId: number;
  notes?: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
  (e: 'update:notes', value: string): void;
}>();

const localNotes = ref<string>(props.notes || '');
const isUpdating = ref<boolean>(false);

const updateNotes = async () => {
  if (isUpdating.value) return;

  isUpdating.value = true;

  try {
    const response = await fetch(route('admin.orders.addNotes', props.orderId), {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({ notes: localNotes.value }),
    });

    const data = await response.json();

    if (data.success) {
      emit('update:notes', localNotes.value);
      toast.success(data.message || 'Notes updated successfully');
    } else {
      toast.error(data.message || 'Failed to update notes');
    }
  } catch (error) {
    console.error('Error updating notes:', error);
    toast.error('Failed to update notes');
  } finally {
    isUpdating.value = false;
  }
};
</script>
