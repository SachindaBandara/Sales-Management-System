<template>
    <Head title="Admin Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Admin Dashboard
                </h2>
                <div class="flex items-center space-x-2 text-sm text-gray-500">
                    <CalendarIcon class="h-4 w-4" />
                    <span>{{ currentDate }}</span>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Overview Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <Card class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                                <p class="text-2xl font-bold text-gray-900">${{ formatCurrency(orderStats.total_revenue) }}</p>
                            </div>
                            <div class="p-3 bg-green-100 rounded-full">
                                <DollarSignIcon class="h-6 w-6 text-green-600" />
                            </div>
                        </div>
                    </Card>

                    <Card class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Today's Revenue</p>
                                <p class="text-2xl font-bold text-gray-900">${{ formatCurrency(orderStats.todays_revenue) }}</p>
                            </div>
                            <div class="p-3 bg-blue-100 rounded-full">
                                <TrendingUpIcon class="h-6 w-6 text-blue-600" />
                            </div>
                        </div>
                    </Card>

                    <Card class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Orders</p>
                                <p class="text-2xl font-bold text-gray-900">{{ orderStats.total_orders.toLocaleString() }}</p>
                            </div>
                            <div class="p-3 bg-purple-100 rounded-full">
                                <ShoppingBagIcon class="h-6 w-6 text-purple-600" />
                            </div>
                        </div>
                    </Card>

                    <Card class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Today's Orders</p>
                                <p class="text-2xl font-bold text-gray-900">{{ orderStats.todays_orders }}</p>
                            </div>
                            <div class="p-3 bg-orange-100 rounded-full">
                                <PackageIcon class="h-6 w-6 text-orange-600" />
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Order Status Overview -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
                    <Card class="p-4">
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Pending</p>
                            <p class="text-xl font-bold text-yellow-600">{{ orderStats.pending_orders }}</p>
                        </div>
                    </Card>
                    <Card class="p-4">
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Processing</p>
                            <p class="text-xl font-bold text-blue-600">{{ orderStats.processing_orders }}</p>
                        </div>
                    </Card>
                    <Card class="p-4">
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Shipped</p>
                            <p class="text-xl font-bold text-purple-600">{{ orderStats.shipped_orders }}</p>
                        </div>
                    </Card>
                    <Card class="p-4">
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Delivered</p>
                            <p class="text-xl font-bold text-green-600">{{ orderStats.delivered_orders }}</p>
                        </div>
                    </Card>
                    <Card class="p-4">
                        <div class="text-center">
                            <p class="text-sm text-gray-600">Cancelled</p>
                            <p class="text-xl font-bold text-red-600">{{ orderStats.cancelled_orders }}</p>
                        </div>
                    </Card>
                    <Card class="p-4">
                        <div class="text-center">
                            <Link :href="route('admin.orders.index')" class="block hover:bg-gray-50 transition-colors">
                                <p class="text-sm text-gray-600">Manage</p>
                                <p class="text-xl font-bold text-blue-600">Orders</p>
                                <ExternalLinkIcon class="h-4 w-4 mx-auto mt-1 text-gray-400" />
                            </Link>
                        </div>
                    </Card>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Daily Revenue Chart -->
                    <Card class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Revenue</h3>
                            <div class="flex items-center space-x-2">
                                <Select v-model="revenuePeriod" @update:modelValue="updateRevenueChart">
                                    <SelectTrigger class="w-[180px]">
                                        <SelectValue placeholder="Select period" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="7days">Last 7 Days</SelectItem>
                                        <SelectItem value="1month">Last Month</SelectItem>
                                        <SelectItem value="6months">Last 6 Months</SelectItem>
                                        <SelectItem value="1year">Last Year</SelectItem>
                                        <SelectItem value="lifetime">Lifetime</SelectItem>
                                    </SelectContent>
                                </Select>
                                <Button variant="outline" size="sm" @click="updateRevenueChart">
                                    <RefreshCwIcon class="h-4 w-4 mr-1" />
                                    Refresh
                                </Button>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas ref="revenueChart"></canvas>
                        </div>
                    </Card>

                    <!-- Daily Orders Chart -->
                    <Card class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Orders</h3>
                            <div class="flex items-center space-x-2">
                                <Select v-model="ordersPeriod" @update:modelValue="updateOrdersChart">
                                    <SelectTrigger class="w-[180px]">
                                        <SelectValue placeholder="Select period" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="7days">Last 7 Days</SelectItem>
                                        <SelectItem value="1month">Last Month</SelectItem>
                                        <SelectItem value="6months">Last 6 Months</SelectItem>
                                        <SelectItem value="1year">Last Year</SelectItem>
                                        <SelectItem value="lifetime">Lifetime</SelectItem>
                                    </SelectContent>
                                </Select>
                                <Button variant="outline" size="sm" @click="updateOrdersChart">
                                    <RefreshCwIcon class="h-4 w-4 mr-1" />
                                    Refresh
                                </Button>
                            </div>
                        </div>
                        <div class="h-64">
                            <canvas ref="ordersChart"></canvas>
                        </div>
                    </Card>
                </div>

                <!-- User Management Section -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <Card class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">User Statistics</h3>
                            <UsersIcon class="h-5 w-5 text-gray-400" />
                        </div>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Total Customers</span>
                                <span class="font-semibold text-blue-600">{{ stats.total_customers }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Active Customers</span>
                                <span class="font-semibold text-green-600">{{ stats.active_customers }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Total Admins</span>
                                <span class="font-semibold text-purple-600">{{ stats.total_admins }}</span>
                            </div>
                            <Button class="w-full mt-4" @click="$inertia.visit(route('admin.users.index'))">
                                <UsersIcon class="h-4 w-4 mr-2" />
                                Manage Users
                            </Button>
                        </div>
                    </Card>

                    <div class="lg:col-span-2">
                        <Card class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Registrations</h3>
                            <RecentRegistrationsTable :users="stats.recent_registrations" />
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { Head, Link, } from '@inertiajs/vue3';
import { Chart, registerables } from 'chart.js';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import RecentRegistrationsTable from '@/components/Admin/Users/RecentRegistrationsTable.vue';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    CalendarIcon,
    DollarSignIcon,
    TrendingUpIcon,
    ShoppingBagIcon,
    PackageIcon,
    RefreshCwIcon,
    ExternalLinkIcon,
    UsersIcon
} from 'lucide-vue-next';
import axios from 'axios';

// Register Chart.js components
Chart.register(...registerables);

interface User {
    id: number;
    name: string;
    email: string;
    created_at: string;
}

interface Stats {
    total_customers: number;
    active_customers: number;
    total_admins: number;
    recent_registrations: User[];
}

interface OrderStats {
    total_orders: number;
    pending_orders: number;
    processing_orders: number;
    shipped_orders: number;
    delivered_orders: number;
    cancelled_orders: number;
    todays_orders: number;
    this_month_orders: number;
    total_revenue: number;
    todays_revenue: number;
    this_month_revenue: number;
}

interface ChartData {
    labels: string[];
    data: number[];
}

const props = defineProps<{
    stats: Stats;
    orderStats: OrderStats;
    dailyRevenueData?: number[];
    dailyOrdersData?: number[];
    monthlyRevenueData?: number[];
    monthlyOrdersData?: number[];
}>();

// Chart refs
const revenueChart = ref<HTMLCanvasElement>();
const ordersChart = ref<HTMLCanvasElement>();

// Chart instances
let revenueChartInstance: Chart | null = null;
let ordersChartInstance: Chart | null = null;

// Reactive state for period selection
const revenuePeriod = ref('7days');
const ordersPeriod = ref('7days');

// Reactive chart data
const revenueChartData = ref<ChartData>({ labels: [], data: [] });
const ordersChartData = ref<ChartData>({ labels: [], data: [] });

// Current date for display
const currentDate = computed(() => {
    return new Date().toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
});

// Format currency
const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
};

// Fetch chart data from server
const fetchChartData = async (type: 'revenue' | 'orders', period: string) => {
    try {
        const response = await axios.get(route('admin.dashboard.chart-data'), {
            params: { type, period }
        });
        return response.data;
    } catch (error) {
        console.error(`Error fetching ${type} chart data:`, error);
        return { labels: [], data: [] };
    }
};

// Create revenue chart
const createRevenueChart = () => {
    if (!revenueChart.value) return;

    const ctx = revenueChart.value.getContext('2d');
    if (!ctx) return;

    if (revenueChartInstance) {
        revenueChartInstance.destroy();
    }

    revenueChartInstance = new Chart(ctx, {
        type: revenuePeriod.value === '7days' || revenuePeriod.value === '1month' ? 'line' : 'bar',
        data: {
            labels: revenueChartData.value.labels,
            datasets: [{
                label: 'Revenue ($)',
                data: revenueChartData.value.data,
                borderColor: '#3B82F6',
                backgroundColor: revenuePeriod.value === '7days' || revenuePeriod.value === '1month'
                    ? 'rgba(59, 130, 246, 0.1)'
                    : '#3B82F6',
                borderWidth: revenuePeriod.value === '7days' || revenuePeriod.value === '1month' ? 3 : 1,
                fill: revenuePeriod.value === '7days' || revenuePeriod.value === '1month',
                tension: revenuePeriod.value === '7days' || revenuePeriod.value === '1month' ? 0.4 : 0,
                pointBackgroundColor: '#3B82F6',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: revenuePeriod.value === '7days' || revenuePeriod.value === '1month' ? 6 : 0,
                pointHoverRadius: revenuePeriod.value === '7days' || revenuePeriod.value === '1month' ? 8 : 0,
                borderRadius: revenuePeriod.value === '7days' || revenuePeriod.value === '1month' ? 0 : 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `$${context.parsed.y.toLocaleString()}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            const numValue = typeof value === 'number' ? value : parseFloat(value);
                            return numValue >= 1000
                                ? '$' + (numValue / 1000).toFixed(0) + 'K'
                                : '$' + numValue.toLocaleString();
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
};

// Create orders chart
const createOrdersChart = () => {
    if (!ordersChart.value) return;

    const ctx = ordersChart.value.getContext('2d');
    if (!ctx) return;

    if (ordersChartInstance) {
        ordersChartInstance.destroy();
    }

    ordersChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ordersChartData.value.labels,
            datasets: [{
                label: 'Orders',
                data: ordersChartData.value.data,
                backgroundColor: '#10B981',
                borderColor: '#059669',
                borderWidth: 1,
                borderRadius: 4,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
};

// Update revenue chart
const updateRevenueChart = async () => {
    const data = await fetchChartData('revenue', revenuePeriod.value);
    revenueChartData.value = {
        labels: data.labels,
        data: data.data
    };
    createRevenueChart();
};

// Update orders chart
const updateOrdersChart = async () => {
    const data = await fetchChartData('orders', ordersPeriod.value);
    ordersChartData.value = {
        labels: data.labels,
        data: data.data
    };
    createOrdersChart();
};

// Initialize charts on mount
onMounted(async () => {
    // Initialize with default period data
    revenueChartData.value = {
        labels: props.dailyRevenueData?.map((_, i) => {
            const date = new Date();
            date.setDate(date.getDate() - (6 - i));
            return date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
        }) || [],
        data: props.dailyRevenueData || []
    };
    ordersChartData.value = {
        labels: props.dailyOrdersData?.map((_, i) => {
            const date = new Date();
            date.setDate(date.getDate() - (6 - i));
            return date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
        }) || [],
        data: props.dailyOrdersData || []
    };

    // Create initial charts
    setTimeout(() => {
        createRevenueChart();
        createOrdersChart();
    }, 100);
});
</script>
