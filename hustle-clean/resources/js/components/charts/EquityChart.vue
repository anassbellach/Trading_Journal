<script setup lang="ts">
import { computed, ref } from 'vue'
import VueApexCharts from 'vue-apexcharts'
import type { EquityCurvePoint } from '@/types'

const props = defineProps<{
    data: EquityCurvePoint[]
    loading?: boolean
}>()

const activePeriod = ref<'1W' | '1M' | '3M' | 'YTD' | 'ALL'>('1M')

const periods = ['1W', '1M', '3M', 'YTD', 'ALL'] as const

const filteredData = computed(() => {
    const now = new Date()
    const cutoffs: Record<string, Date> = {
        '1W':  new Date(now.getTime() - 7 * 86400000),
        '1M':  new Date(now.getTime() - 30 * 86400000),
        '3M':  new Date(now.getTime() - 90 * 86400000),
        'YTD': new Date(now.getFullYear(), 0, 1),
        'ALL': new Date(0),
    }
    const cutoff = cutoffs[activePeriod.value]
    return props.data.filter(p => new Date(p.date) >= cutoff)
})

const series = computed(() => [{
    name: 'Equity',
    data: filteredData.value.map(p => ({ x: new Date(p.date).getTime(), y: p.equity })),
}])

const chartOptions = computed(() => ({
    chart: {
        type: 'area',
        toolbar: { show: false },
        zoom: { enabled: false },
        animations: { enabled: true, easing: 'easeinout', speed: 600 },
        background: 'transparent',
        fontFamily: 'DM Sans, sans-serif',
    },
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2.5, colors: ['#00C896'] },
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.18,
            opacityTo: 0.0,
            stops: [0, 90, 100],
            colorStops: [
                { offset: 0,   color: '#00C896', opacity: 0.18 },
                { offset: 100, color: '#00C896', opacity: 0.0 },
            ],
        },
    },
    xaxis: {
        type: 'datetime',
        labels: {
            style: { colors: 'rgba(255,255,255,0.3)', fontSize: '11px' },
            datetimeFormatter: { day: 'dd MMM', month: 'MMM yy' },
        },
        axisBorder: { show: false },
        axisTicks:  { show: false },
    },
    yaxis: {
        labels: {
            style: { colors: 'rgba(255,255,255,0.3)', fontSize: '11px' },
            formatter: (v: number) => '$' + v.toLocaleString('nl-NL', { minimumFractionDigits: 0 }),
        },
    },
    grid: {
        borderColor: 'rgba(255,255,255,0.04)',
        strokeDashArray: 4,
        yaxis: { lines: { show: true } },
        xaxis: { lines: { show: false } },
    },
    tooltip: {
        theme: 'dark',
        x: { format: 'dd MMM yyyy' },
        y: { formatter: (v: number) => '$' + v.toLocaleString('nl-NL', { minimumFractionDigits: 2 }) },
    },
    markers: {
        size: 0,
        hover: { size: 5, sizeOffset: 3, fillColor: '#00C896', strokeColor: '#0A0C10', strokeWidth: 2 },
    },
}))
</script>

<template>
    <div class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-text-primary">Equity Curve</h3>
            <div class="flex gap-1">
                <button
                    v-for="p in periods"
                    :key="p"
                    :class="['btn btn-xs', activePeriod === p ? 'bg-brand-muted border border-brand-border text-brand' : 'btn-ghost text-text-tertiary']"
                    @click="activePeriod = p"
                >{{ p }}</button>
            </div>
        </div>

        <!-- Skeleton -->
        <div v-if="loading" class="flex flex-col gap-2">
            <div class="skeleton h-48 w-full rounded-xl" />
        </div>

        <VueApexCharts
            v-else-if="series[0].data.length > 0"
            type="area"
            :options="chartOptions"
            :series="series"
            height="200"
        />

        <div v-else class="empty-state h-48">
            <p class="text-text-muted text-sm">Nog geen trades om te tonen</p>
        </div>
    </div>
</template>
