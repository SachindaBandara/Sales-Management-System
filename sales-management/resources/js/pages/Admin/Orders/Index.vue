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
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                        <span class="text-white font-bold text-sm">T</span>
                                    </div>
                                </div>
                                <div class="ml-5">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total Orders</dt>
                                        <dd class="text-lg font-semibold text-gray-900">{{ statistics.total_orders }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                        <span class="text-white font-bold text-sm">P</span>
                                    </div>
                                </div>
                                <div class="ml-5">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Pending Orders</dt>
                                        <dd class="text-lg font-semibold text-gray-900">{{ statistics.pending_orders }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                        <span class="text-white font-bold text-sm">C</span>
                                    </div>
                                </div>
                                <div class="ml-5">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Completed Orders</dt>
                                        <dd class="text-lg font-semibold text-gray-900">{{ statistics.completed_orders }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                        <span class="text-white font-bold text-sm">R</span>
                                    </div>
                                </div>
                                <div class="ml-5">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total Revenue</dt>
                                        <dd class="text-lg font-semibold text-gray-900">${{ formatCurrency(statistics.total_revenue) }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing {{ orders.from }} to {{ orders.to }} of {{ orders.total }} results
                        </div>
                        <div class="flex space-x-1">
                            <Link v-for="link in orders.links" :key="link.label"
                                  :href="link.url ?? ''"
                                  :class="[
                                      'px-3 py-2 text-sm border rounded transition-colors duration-150',
                                      link.active ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                                      !link.url ? 'pointer-events-none opacity-50' : ''
                                  ]"
                            >
                                <span v-if="link.label === '« Previous'">« Previous</span>
                                <span v-else-if="link.label === 'Next »'">Next »</span>
                                <span v-else>{{ link.label.replace(/(<([^>]+)>)/gi, '') }}</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Notifications -->
        <Toaster />
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { Toaster } from '@/components/ui/sonner'
import { toast } from 'sonner'
import { Download } from 'lucide-vue-next'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import OrdersTable from '@/components/Admin/Orders/OrdersTable.vue'
import type { Order } from '@/types/order'


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
    links: PaginationLink[]
}

interface Statistics {
    total_orders: number
    pending_orders: number
    completed_orders: number
    total_revenue: number
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
    if (!confirm('Are you sure you want to delete this order? This action cannot be undone.')) return

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

const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount)
}
</script>