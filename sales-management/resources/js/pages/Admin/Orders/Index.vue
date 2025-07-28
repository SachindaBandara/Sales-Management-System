<template>
  <Head title="Orders Management" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Orders Management
        </h2>
        <div class="flex space-x-3">
          <!-- Export Button -->
          <ExportButton :is-exporting="isExporting" @click="exportOrders" />
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Statistics Cards -->
        <OrderStatistics :statistics="statistics" />

        <!-- Filters -->
        <OrderFilters
          :filters="filters"
          @search="applyFilters"
          @clear="clearFilters"
          @filter-change="updateFilters"
        />

        <!-- Orders Table -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <OrdersTable
            :orders="orders.data"
            :pagination="orders"
            @view-order="viewOrder"
            @update-order-status="updateOrderStatus"
            @update-payment-status="updatePaymentStatus"
            @delete-order="deleteOrder"
            @paginate="paginate"
            @bulk-invoice-download="handleBulkInvoiceDownload"
          />
        </div>
      </div>
    </div>

    <!-- Toast Notifications -->
    <Toaster />
  </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Toaster } from '@/components/ui/sonner';
import { toast } from 'sonner';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import OrdersTable from '@/components/Admin/Orders/OrdersTable.vue';
import OrderStatistics from '@/components/Admin/Orders/OrderStatistics.vue';
import OrderFilters from '@/components/Admin/Orders/OrderFilters.vue';
import ExportButton from '@/components/Common/ExportButton.vue';
import type { Order, Statistics } from '@/types/order';

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface OrdersPagination {
  data: Order[];
  from: number;
  to: number;
  total: number;
  current_page: number;
  prev_page_url: string | null;
  next_page_url: string | null;
  links: PaginationLink[];
}

interface Filters {
  search?: string;
  status?: string;
  payment_status?: string;
  date_from?: string;
  date_to?: string;
}

const props = defineProps<{
  orders: OrdersPagination;
  statistics: Statistics;
  filters: Filters;
}>();

const page = usePage();

const filters = reactive<Filters>({
  search: props.filters.search || '',
  status: props.filters.status || '',
  payment_status: props.filters.payment_status || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || ''
});

const orders = ref<OrdersPagination>(props.orders);
const statistics = ref<Statistics>(props.statistics);
const isExporting = ref(false);

// Handle flash messages
onMounted(() => {
  const flashMessages = page.props.flash as any;
  if (flashMessages?.success) {
    toast.success(flashMessages.success);
  }
  if (flashMessages?.error) {
    toast.error(flashMessages.error);
  }
});

const updateFilters = (newFilters: Filters) => {
  filters.search = newFilters.search || '';
  filters.status = newFilters.status || '';
  filters.payment_status = newFilters.payment_status || '';
  filters.date_from = newFilters.date_from || '';
  filters.date_to = newFilters.date_to || '';
};

const applyFilters = () => {
  router.get(route('admin.orders.index'), filters, {
    preserveState: true,
    replace: true,
    onSuccess: (page) => {
      orders.value = page.props.orders as OrdersPagination;
    }
  });
};

const clearFilters = () => {
  (Object.keys(filters) as Array<keyof Filters>).forEach(key => {
    filters[key] = '';
  });
  applyFilters();
};

const viewOrder = (id: number) => {
  router.get(route('admin.orders.show', id));
};

const updateOrderStatus = (id: number, status: string) => {
  router.put(route('admin.orders.updateStatus', id), { status }, {
    preserveState: true,
    onSuccess: () => {
      applyFilters();
      toast.success('Order status updated successfully');
    }
  });
};

const updatePaymentStatus = (id: number, payment_status: string) => {
  router.put(route('admin.orders.updatePaymentStatus', id), { payment_status }, {
    preserveState: true,
    onSuccess: () => {
      applyFilters();
      toast.success('Payment status updated successfully');
    }
  });
};

const deleteOrder = (id: number) => {
  if (!confirm('Are you sure you want to delete this order? This action cannot be undone.')) return;

  router.delete(route('admin.orders.destroy', id), {
    preserveState: true,
    onSuccess: () => {
      applyFilters();
      toast.success('Order deleted successfully');
    }
  });
};

const exportOrders = () => {
  if (isExporting.value) return;

  isExporting.value = true;

  const params = new URLSearchParams();
  if (filters.search) params.append('search', filters.search);
  if (filters.status) params.append('status', filters.status);
  if (filters.payment_status) params.append('payment_status', filters.payment_status);
  if (filters.date_from) params.append('date_from', filters.date_from);
  if (filters.date_to) params.append('date_to', filters.date_to);

  const exportUrl = route('admin.orders.export') + (params.toString() ? '?' + params.toString() : '');

  const link = document.createElement('a');
  link.href = exportUrl;
  link.download = '';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);

  setTimeout(() => {
    isExporting.value = false;
  }, 2000);
};

const handleBulkInvoiceDownload = async (orderIds: number[]) => {
  try {
    const bulkDownloadUrl = route('admin.orders.invoice.bulk-download');
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = bulkDownloadUrl;
    form.style.display = 'none';

    // Add CSRF token
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (token) {
      const tokenInput = document.createElement('input');
      tokenInput.type = 'hidden';
      tokenInput.name = '_token';
      tokenInput.value = token;
      form.appendChild(tokenInput);
    }

    // Add order_ids
    const orderIdsInput = document.createElement('input');
    orderIdsInput.type = 'hidden';
    orderIdsInput.name = 'order_ids';
    orderIdsInput.value = JSON.stringify(orderIds);
    form.appendChild(orderIdsInput);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);

    toast.success('Bulk invoice download started');
  } catch (error) {
    console.error('Bulk invoice download failed:', error);
    toast.error('Failed to download invoices. Please try again.');
  }
};

const paginate = (url: string) => {
  router.get(url, {}, {
    preserveState: true,
    replace: true,
    onSuccess: (page) => {
      orders.value = page.props.orders as OrdersPagination;
    }
  });
};
</script>
