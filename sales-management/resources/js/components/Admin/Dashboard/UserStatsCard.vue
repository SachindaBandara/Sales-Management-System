<template>
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
</template>

<script setup lang="ts">
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { UsersIcon } from 'lucide-vue-next';

interface Props {
    stats: {
        total_customers: number;
        active_customers: number;
        total_admins: number;
    };
}

defineProps<Props>();
</script>
