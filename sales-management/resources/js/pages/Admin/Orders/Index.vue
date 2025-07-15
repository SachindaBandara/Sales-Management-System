<template>
    <Head title="Orders Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Orders Management
                </h2>
                <div class="flex gap-2">
                    <button @click="exportOrders" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center gap-2">
                        <Download class="w-4 h-4" />
                        Export CSV
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Statistics Cards -->
                <OrderStatistics :statistics="statistics" />

                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                            <div>
                                <input
                                    v-model="filters.search"
                                    type="text"
                                    placeholder="Search orders..."
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                            <div>
                                <select v-model="filters.status" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="processing">Processing</option>
                                    <option value="shipped">Shipped</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div>
                                <select v-model="filters.payment_status" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">All Payment Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="paid">Paid</option>
                                    <option value="failed">Failed</option>
                                    <option value="refunded">Refunded</option>
                                </select>
                            </div>
                            <div>
                                <input
                                    v-model="filters.date_from"
                                    type="date"
                                    placeholder="From Date"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                            <div>
                                <input
                                    v-model="filters.date_to"
                                    type="date"
                                    placeholder="To Date"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                                    Search
                                </button>
                                <button type="button" @click="clearFilters" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition-colors duration-200">
                                    Clear
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Orders Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <OrdersTable
                        :orders="orders.data"
                        @view-order="viewOrder"
                        @update-order-status="updateOrderStatus"
                        @update-payment-status="updatePaymentStatus"
                        @delete-order="deleteOrder"
                    />
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t bg-gray-50">
                    <OrderPagination
                        :orders="orders"
                        @change-page="changePage"
                    />
                </div>
            </div>
        </div>

        <!-- Toast Notifications -->
        <Toaster />
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { Toaster } from '@/components/ui/sonner'
import { toast } from 'sonner'
import { Download } from 'lucide-vue-next'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import OrdersTable from '@/components/Admin/Orders/OrdersTable.vue'
import OrderPagination from '@/components/Admin/Orders/OrderPagination.vue'
import OrderStatistics from '@/components/Admin/Orders/OrderStatistics.vue'
import type { Order, Statistics } from '@/types/order'

interface PaginationLink {
    url: string | null
    label: string
    active: boolean
}

interface OrdersPagination {
    data: Order[]
    from: number
    to: number
    total: number
    current_page: number
    prev_page_url: string | null
    next_page_url: string | null
    links: PaginationLink[]
}

interface Filters {
    status: string
    payment_status: string
    search: string
    date_from: string
    date_to: string
}

const props = defineProps<{
    orders: OrdersPagination
    statistics: Statistics
    filters: Filters
}>()

const page = usePage()

const filters = reactive<Filters>({
    status: props.filters.status || '',
    payment_status: props.filters.payment_status || '',
    search: props.filters.search || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || ''
})

const orders = ref<OrdersPagination>(props.orders)
const statistics = ref<Statistics>(props.statistics)

// Handle flash messages
onMounted(() => {
    const flashMessages = page.props.flash as any
    if (flashMessages?.success) {
        toast.success(flashMessages.success)
    }
    if (flashMessages?.error) {
        toast.error(flashMessages.error)
    }
})

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

const viewOrder = (id: number) => {
    router.get(route('admin.orders.show', id))
}

const updateOrderStatus = (id: number, status: string) => {
    router.put(route('admin.orders.updateStatus', id), { status }, {
        preserveState: true,
        onSuccess: () => {
            // Refresh the orders data
            applyFilters()
        }
    })
}

const updatePaymentStatus = (id: number, payment_status: string) => {
    router.put(route('admin.orders.updatePaymentStatus', id), { payment_status }, {
        preserveState: true,
        onSuccess: () => {
            // Refresh the orders data
            applyFilters()
        }
    })
}

const deleteOrder = (id: number) => {
    if (!confirm('Are you sure you want to delete this order? This action cannot be undone.')) return

    router.delete(route('admin.orders.destroy', id), {
        preserveState: true,
        onSuccess: () => {
            // Refresh the orders data
            applyFilters()
        }
    })
}

const changePage = (page: number) => {
    const newFilters = { ...filters, page: page.toString() }
    router.get(route('admin.orders.index'), newFilters, {
        preserveState: true,
        replace: true,
        onSuccess: (page) => {
            orders.value = page.props.orders as OrdersPagination
        }
    })
}

const exportOrders = () => {
    const params = new URLSearchParams(filters as any).toString()
    window.location.href = route('admin.orders.export') + (params ? `?${params}` : '')
}
</script>