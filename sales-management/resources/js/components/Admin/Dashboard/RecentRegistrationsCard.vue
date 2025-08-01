<template>
    <Card class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Registrations</h3>
        <div class="w-full overflow-x-auto">
            <RecentRegistrationsTable :users="users" />
        </div>
    </Card>
</template>

<script setup lang="ts">
import { Card } from '@/components/ui/card';
import RecentRegistrationsTable from '@/components/Admin/Users/RecentRegistrationsTable.vue';

interface Props {
    users: Array<{
        id: number;
        name: string;
        email: string;
        created_at: string;
    }>;
}

defineProps<Props>();
</script>
