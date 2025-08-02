<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <DashboardHeader />
        </template>

        <div class="py-4 sm:py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Overview Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
                    <StatsCard
                        title="Total Revenue"
                        :value="orderStats.total_revenue"
                        :icon="DollarSignIcon"
                        backgroundColor="bg-green-50"
                        iconColor="text-green-600"
                        color="text-gray-900"
                        isCurrency
                    />
                    <StatsCard
                        title="Today's Revenue"
                        :value="orderStats.todays_revenue"
                        :icon="TrendingUpIcon"
                        backgroundColor="bg-blue-50"
                        iconColor="text-blue-600"
                        color="text-gray-900"
                        isCurrency
                    />
                    <StatsCard
                        title="Total Orders"
                        :value="orderStats.total_orders"
                        :icon="ShoppingBagIcon"
                        backgroundColor="bg-purple-50"
                        iconColor="text-purple-600"
                        color="text-gray-900"
                    />
                    <StatsCard
                        title="Today's Orders"
                        :value="orderStats.todays_orders"
                        :icon="PackageIcon"
                        backgroundColor="bg-orange-50"
                        iconColor="text-orange-600"
                        color="text-gray-900"
                    />
                </div>

                <!-- Order Status Overview -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4 mb-6 sm:mb-8">
                    <OrderStatusCard title="Pending" :value="orderStats.pending_orders" textColor="text-yellow-600" />
                    <OrderStatusCard title="Processing" :value="orderStats.processing_orders" textColor="text-blue-600" />
                    <OrderStatusCard title="Shipped" :value="orderStats.shipped_orders" textColor="text-purple-600" />
                    <OrderStatusCard title="Delivered" :value="orderStats.delivered_orders" textColor="text-green-600" />
                    <OrderStatusCard title="Cancelled" :value="orderStats.cancelled_orders" textColor="text-red-600" />
                    <OrderStatusCard title="Manage" value="Orders" textColor="text-blue-600" isLink />
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6 sm:mb-8">
                    <ChartCard
                        title="Revenue"
                        type="revenue"
                        :initialData="dailyRevenueData"
                        @update="updateRevenueData"
                        class="min-h-[300px] sm:min-h-[350px]"
                    />
                    <ChartCard
                        title="Orders"
                        type="orders"
                        :initialData="dailyOrdersData"
                        @update="updateOrdersData"
                        class="min-h-[300px] sm:min-h-[350px]"
                    />
                </div>

                <!-- User Management Section -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
                    <UserStatsCard :stats="stats" class="lg:col-span-1" />
                    <RecentRegistrationsCard :users="stats.recent_registrations" class="lg:col-span-3" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import DashboardHeader from '@/components/Admin/Dashboard/DashboardHeader.vue';
import StatsCard from '@/components/Common/StatsCard.vue';
import OrderStatusCard from '@/components/Admin/Dashboard/OrderStatusCard.vue';
import ChartCard from '@/components/Admin/Dashboard/ChartCard.vue';
import UserStatsCard from '@/components/Admin/Dashboard/UserStatsCard.vue';
import RecentRegistrationsCard from '@/components/Admin/Dashboard/RecentRegistrationsCard.vue';
import { DollarSignIcon, TrendingUpIcon, ShoppingBagIcon, PackageIcon } from 'lucide-vue-next';

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
    dailyRevenueData: number[];
    dailyOrdersData: number[];
    monthlyRevenueData?: number[];
    monthlyOrdersData?: number[];
}>();

const dailyRevenueData = ref(props.dailyRevenueData);
const dailyOrdersData = ref(props.dailyOrdersData);

const updateRevenueData = (data: ChartData) => {
    dailyRevenueData.value = data.data;
};

const updateOrdersData = (data: ChartData) => {
    dailyOrdersData.value = data.data;
};
</script>

<style scoped>
/* Ensure smooth transitions for responsive layouts */
@media (max-width: 640px) {
    .grid-cols-2 {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
}
</style>
