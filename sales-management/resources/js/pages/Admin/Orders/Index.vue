<template>
  <AuthenticatedLayout>
    <div class="container mx-auto px-4 py-8">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Orders Management</h1>
        <Button @click="exportOrders" variant="outline" class="gap-2">
          <Download class="w-4 h-4" />
          Export CSV
        </Button>
      </div>

      <OrderStatistics :statistics="statistics" />
      <OrderFilters v-model:filters="filters" @apply-filters="applyFilters" @clear-filters="clearFilters" />
      <OrdersTable
        :orders="orders.data"
        @view-order="viewOrder"
        @update-order-status="updateOrderStatus"
        @update-payment-status="updatePaymentStatus"
        @delete-order="deleteOrder"
      />
      <OrderPagination
        :orders="orders"
        @change-page="changePage"
      />

      <Toaster />
    </div>
  </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Toaster } from '@/components/ui/sonner'
import { toast } from 'sonner'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import OrderStatistics from '@/components/Admin/Orders/OrderStatistics.vue'
import OrderFilters from '@/components/Admin/Orders/OrderFilter.vue'
import OrdersTable from '@/components/Admin/Orders/OrdersTable.vue'
import OrderPagination from '@/components/Admin/Orders/OrderPagination.vue'
import { Download } from 'lucide-vue-next'
import { OrdersPagination, Statistics, Filters } from '@/types/order'

const props = defineProps<{
  orders: OrdersPagination
  statistics: Statistics
  filters: Filters
}>()

const filters = reactive<Filters>({
  status: props.filters.status || '',
  payment_status: props.filters.payment_status || '',
  search: props.filters.search || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || ''
})

const orders = ref<OrdersPagination>(props.orders)
const statistics = ref<Statistics>(props.statistics)

const applyFilters = () => {
  router.get(route('admin.orders.index'), filters, {
    preserveState: true,
    replace: true,
    onSuccess: (page) => {
      orders.value = page.props.orders as OrdersPagination
    }
  })
}

const clearFilters = () => {
  (Object.keys(filters) as Array<keyof Filters>).forEach(key => {
    filters[key] = ''
  })
  applyFilters()
}

const changePage = (page: number) => {
  router.get(route('admin.orders.index'), { ...filters, page }, {
    preserveState: true,
    replace: true,
    onSuccess: (page) => {
      orders.value = page.props.orders as OrdersPagination
    }
  })
}

const viewOrder = (id: number) => {
  router.get(route('admin.orders.show', id))
}

const updateOrderStatus = async (id: number, status: string) => {
  try {
    await router.put(route('admin.orders.updateStatus', id), { status }, {
      preserveState: true,
      onSuccess: () => {
        toast.success('Order status updated successfully')
        applyFilters()
      },
      onError: () => {
        toast.error('Failed to update order status')
      }
    })
  } catch (error) {
    console.error('Error updating order status:', error)
  }
}

const updatePaymentStatus = async (id: number, payment_status: string) => {
  try {
    await router.put(route('admin.orders.updatePaymentStatus', id), { payment_status }, {
      preserveState: true,
      onSuccess: () => {
        toast.success('Payment status updated successfully')
        applyFilters()
      },
      onError: () => {
        toast.error('Failed to update payment status')
      }
    })
  } catch (error) {
    console.error('Error updating payment status:', error)
  }
}

const deleteOrder = async (id: number) => {
  if (!confirm('Are you sure you want to delete this order?')) return

  try {
    await router.delete(route('admin.orders.destroy', id), {
      preserveState: true,
      onSuccess: () => {
        toast.success('Order deleted successfully')
        applyFilters()
      },
      onError: () => {
        toast.error('Failed to delete order')
      }
    })
  } catch (error) {
    console.error('Error deleting order:', error)
  }
}

const exportOrders = () => {
  const params = new URLSearchParams(filters as any).toString()
  window.location.href = route('admin.orders.export') + (params ? `?${params}` : '')
}
</script>