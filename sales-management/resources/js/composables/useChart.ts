import { Ref } from 'vue';
import { Chart, registerables } from 'chart.js';
import axios from 'axios';

Chart.register(...registerables);

interface ChartData {
    labels: string[];
    data: number[];
}

export function useChart(type: 'revenue' | 'orders', chartRef: Ref<HTMLCanvasElement | null>) {
    let chartInstance: Chart | null = null;

    const fetchChartData = async (period: string): Promise<ChartData> => {
        try {
            const response = await axios.get(route('admin.dashboard.chart-data'), {
                params: { type, period }
            });
            return response.data as ChartData;
        } catch (error) {
            console.error(`Error fetching ${type} chart data:`, error);
            return { labels: [], data: [] };
        }
    };

    const createChart = (data: ChartData, period: string) => {
        if (!chartRef.value) return;

        const ctx = chartRef.value.getContext('2d');
        if (!ctx) return;

        if (chartInstance) {
            chartInstance.destroy();
        }

        chartInstance = new Chart(ctx, {
            type: type === 'revenue' && (period === '7days' || period === '1month') ? 'line' : 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: type === 'revenue' ? 'Revenue ($)' : 'Orders',
                    data: data.data,
                    borderColor: type === 'revenue' ? '#3B82F6' : '#10B981',
                    backgroundColor: type === 'revenue'
                        ? (period === '7days' || period === '1month' ? 'rgba(59, 130, 246, 0.1)' : '#3B82F6')
                        : '#10B981',
                    borderWidth: type === 'revenue' && (period === '7days' || period === '1month') ? 3 : 1,
                    fill: type === 'revenue' && (period === '7days' || period === '1month'),
                    tension: type === 'revenue' && (period === '7days' || period === '1month') ? 0.4 : 0,
                    pointBackgroundColor: type === 'revenue' ? '#3B82F6' : '#10B981',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: type === 'revenue' && (period === '7days' || period === '1month') ? 6 : 0,
                    pointHoverRadius: type === 'revenue' && (period === '7days' || period === '1month') ? 8 : 0,
                    borderRadius: type === 'revenue' && (period === '7days' || period === '1month') ? 0 : 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (context) => type === 'revenue'
                                ? `$${context.parsed.y.toLocaleString()}`
                                : `${context.parsed.y} orders`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: type === 'revenue'
                            ? {
                                callback: (value) => {
                                    const numValue = typeof value === 'number' ? value : parseFloat(value);
                                    return numValue >= 1000
                                        ? '$' + (numValue / 1000).toFixed(0) + 'K'
                                        : '$' + numValue.toLocaleString();
                                }
                            }
                            : { stepSize: 1 },
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }
                    },
                    x: { grid: { display: false } }
                },
                interaction: { intersect: false, mode: 'index' }
            }
        });
    };

    return { createChart, fetchChartData };
}
