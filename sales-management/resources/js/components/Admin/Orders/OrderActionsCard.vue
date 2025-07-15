<!-- src/components/orders/OrderActionsCard.vue -->
<template>
  <Card>
    <CardHeader>
      <CardTitle>Actions</CardTitle>
    </CardHeader>
    <CardContent>
      <div class="space-y-2">
        <Button @click="sendOrderEmail" variant="outline" class="w-full">
          <Mail class="w-4 h-4 mr-2" />
          Send Order Email
        </Button>
        <Button @click="generateInvoice" variant="outline" class="w-full">
          <FileText class="w-4 h-4 mr-2" />
          Generate Invoice
        </Button>
        <Button
          @click="deleteOrder"
          variant="destructive"
          class="w-full"
          :disabled="isDeleting"
        >
          <Trash2 class="w-4 h-4 mr-2" />
          {{ isDeleting ? 'Deleting...' : 'Delete Order' }}
        </Button>
      </div>
    </CardContent>
  </Card>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Mail, FileText, Trash2 } from 'lucide-vue-next';
import { toast } from 'sonner';

interface Props {
  orderId: number;
}

const props = defineProps<Props>();
const isDeleting = ref<boolean>(false);

const deleteOrder = async () => {
  if (isDeleting.value) return;

  if (!confirm('Are you sure you want to delete this order? This action cannot be undone.')) {
    return;
  }

  isDeleting.value = true;

  try {
    const response = await fetch(route('admin.orders.destroy', props.orderId), {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
    });

    const data = await response.json();

    if (data.success) {
      toast.success(data.message || 'Order deleted successfully');
      router.get(route('admin.orders.index'));
    } else {
      toast.error(data.message || 'Failed to delete order');
    }
  } catch (error) {
    console.error('Error deleting order:', error);
    toast.error('Failed to delete order');
  } finally {
    isDeleting.value = false;
  }
};

const sendOrderEmail = () => {
  toast.info('Order email functionality will be available soon');
};

const generateInvoice = () => {
  toast.info('Invoice generation functionality will be available soon');
};
</script>
