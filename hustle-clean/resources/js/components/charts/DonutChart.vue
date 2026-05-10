<script setup lang="ts">
import { computed } from 'vue'
import VueApexCharts from 'vue-apexcharts'

const props = defineProps<{
    wins: number
    losses: number
    loading?: boolean
}>()

const winRate = computed(() => {
    const total = props.wins + props.losses
    return total > 0 ? ((props.wins / total) * 100).toFixed(1) : '0.0'
})

const series = computed(() => [props.wins, props.losses])

const chartOptions = computed(() => ({
    chart: {
        type: 'donut',
        background: 'transparent',
        animations: { enabled: true, speed: 500 },
        fontFamily: 'DM Sans, sans-serif',
    },
    labels: ['Wins', 'Losses'],
    colors: ['#00C896', 'rgba(255,75,75,0.5)'],
    legend: { show: false },
    dataLabels: { enabled: false },
    stroke: { width: 0 },
    plotOptions: {
        pie: {
            donut: {
                size: '68%',
                labels: {
                    show: true,
                    name: {
                        show: true,
                        offsetY: 20,
                        color: 'rgba(255,255,255,0.4)',
                        fontSize: '12px',
                    },
                    value: {
                        show: true,
                        offsetY: -14,
                        color: '#E8EAF0',
                        fontSize: '26px',
                        fontWeight: '700',
                        formatter: () => `${winRate.value}%`,
                    },
                    total: {
                        show: true,
                        label: 'Win Rate',
                        color: 'rgba(255,255,255,0.4)',
                        fontSize: '12px',
                        formatter: () => `${winRate.value}%`,
                    },
                },
            },
        },
    },
    tooltip: {
        theme: 'dark',
        y: { formatter: (v: number) => `${v} trades` },
    },
}))
</script>

<template>
    <div class="card p-5">
        <h3 class="text-sm font-semibold text-text-primary mb-4">Trade Verdeling</h3>

        <div v-if="loading" class="skeleton h-36 w-36 rounded-full mx-auto" />

        <VueApexCharts
            v-else
            type="donut"
            :options="chartOptions"
            :series="series"
            height="160"
        />

        <div class="space-y-2 mt-3">
            <div v-for="(item, i) in [{ label: 'Wins', count: wins, cls: 'bg-profit' }, { label: 'Losses', count: losses, cls: 'bg-loss/50' }]" :key="i"
                class="flex items-center justify-between py-2 px-3 rounded-lg bg-panel"
            >
                <div class="flex items-center gap-2">
                    <div :class="['w-2 h-2 rounded-full', item.cls]" />
                    <span class="text-xs font-medium text-text-secondary">{{ item.label }}</span>
                </div>
                <span class="text-xs font-bold text-text-primary">{{ item.count }}</span>
            </div>
        </div>
    </div>
</template>
