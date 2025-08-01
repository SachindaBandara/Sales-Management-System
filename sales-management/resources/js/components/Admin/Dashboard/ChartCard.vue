<template>
    <Card class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
            <div class="flex items-center space-x-2">
                <Select v-model="selectedPeriod" @update:modelValue="updateChart">
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
                <Button variant="outline" size="sm" @click="updateChart">
                    <RefreshCwIcon class="h-4 w-4 mr-1" />
                    Refresh
                </Button>
            </div>
        </div>
        <div class="h-64">
            <canvas ref="chartRef"></canvas>
        </div>
    </Card>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { RefreshCwIcon } from 'lucide-vue-next';
import { useChart } from '@/composables/useChart';

interface Props {
    title: string;
    type: 'revenue' | 'orders';
    initialData: number[];
}

interface ChartData {
    labels: string[];
    data: number[];
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'update', data: ChartData): void;
}>();

const selectedPeriod = ref('7days');
const chartRef = ref<HTMLCanvasElement | null>(null);
const { createChart, fetchChartData } = useChart(props.type, chartRef);

const updateChart = async () => {
    const data = await fetchChartData(selectedPeriod.value);
    emit('update', data);
    createChart(data, selectedPeriod.value);
};

onMounted(async () => {
    const initialChartData: ChartData = {
        labels: props.initialData.map((_, i) => {
            const date = new Date();
            date.setDate(date.getDate() - (6 - i));
            return date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
        }),
        data: props.initialData
    };
    createChart(initialChartData, selectedPeriod.value);
});

watch(selectedPeriod, () => updateChart());
</script>
