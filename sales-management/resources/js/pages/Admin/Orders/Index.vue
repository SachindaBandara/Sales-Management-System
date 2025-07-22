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
                    <button
                        @click="exportBrands"
                        :disabled="isExporting"
                        class="bg-green-500 hover:bg-green-700 disabled:bg-green-300 text-white font-bold py-2 px-4 rounded transition-colors duration-200 flex items-center space-x-2"
                    >
                        <svg v-if="isExporting" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span>{{ isExporting ? 'Exporting...' : 'Export Excel' }}</span>
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

const form = reactive({
    search: props.filters.search || '',
    status: props.filters.status || '',
});

const isExporting = ref(false)

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
            applyFilters()
            toast.success('Order status updated successfully')
        }
    })
}

const updatePaymentStatus = (id: number, payment_status: string) => {
    router.put(route('admin.orders.updatePaymentStatus', id), { payment_status }, {
        preserveState: true,
        onSuccess: () => {
            applyFilters()
            toast.success('Payment status updated successfully')
        }
    })
}

const deleteOrder = (id: number) => {
    if (!confirm('Are you sure you want to delete this order? This action cannot be undone.')) return

    router.delete(route('admin.orders.destroy', id), {
        preserveState: true,
        onSuccess: () => {
            applyFilters()
            toast.success('Order deleted successfully')
        }
    })
}

const exportBrands = () => {
    if (isExporting.value) return;

    isExporting.value = true;

    // Create URL with current filters
    const params = new URLSearchParams();
    if (form.search) params.append('search', form.search);
    if (form.status !== '') params.append('status', form.status);

    const exportUrl = route('admin.orders.export') + (params.toString() ? '?' + params.toString() : '');

    // Create a temporary link to trigger download
    const link = document.createElement('a');
    link.href = exportUrl;
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    // Reset loading state after a short delay
    setTimeout(() => {
        isExporting.value = false;
    }, 2000);
};

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

</script>

<style scoped>
/* Custom styles for the orders management page */
.orders-management {
    min-height: 100vh;
    background-color: #5b6977;
}

/* Loading state styles */
.loading-overlay {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    background-color: rgba(255, 255, 255, 0.75);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 50;
}

/* Export button animation */
@keyframes pulse-green {
    0%, 100% {
        background-color: #10b981;
    }
    50% {
        background-color: #059669;
    }
}

.export-button-loading {
    animation: pulse-green 1.5s ease-in-out infinite;
}

/* Filter form responsive adjustments */
@media (max-width: 768px) {
    .filters-form {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }

    .filter-buttons {
        flex-direction: column;
        gap: 0.5rem;
    }
}

/* Table responsive wrapper */
.table-wrapper {
    overflow-x: auto;
}

/* Status badge styles */
.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-pending {
    background-color: #fef3c7;
    color: #92400e;
}

.status-processing {
    background-color: #dbeafe;
    color: #1e40af;
}

.status-shipped {
    background-color: #ede9fe;
    color: #7c3aed;
}

.status-delivered {
    background-color: #dcfce7;
    color: #166534;
}

.status-cancelled {
    background-color: #fee2e2;
    color: #dc2626;
}

/* Payment status badges */
.payment-pending {
    background-color: #fed7aa;
    color: #ea580c;
}

.payment-paid {
    background-color: #dcfce7;
    color: #166534;
}

.payment-failed {
    background-color: #fee2e2;
    color: #dc2626;
}

.payment-refunded {
    background-color: #f3f4f6;
    color: #374151;
}
</style>
