<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import VueApexCharts from 'vue-apexcharts'
import AppLayout from '@/components/layout/AppLayout.vue'
import StatCard from '@/components/common/StatCard.vue'
import type { AnalyticsData } from '@/types'

const props = defineProps<{
    analytics: AnalyticsData
}>()

const activePeriod = ref('month')

// ── Strategy bar chart ──────────────────────────────────────────
const strategyOptions = computed(() => ({
    chart: { type: 'bar', toolbar: { show: false }, background: 'transparent', fontFamily: 'DM Sans, sans-serif' },
    plotOptions: { bar: { borderRadius: 5, horizontal: true, barHeight: '60%' } },
    dataLabels: { enabled: false },
    xaxis: {
        labels: { style: { colors: 'rgba(255,255,255,0.35)', fontSize: '11px' }, formatter: (v: number) => `$${v}` },
        axisBorder: { show: false }, axisTicks: { show: false },
    },
    yaxis: { labels: { style: { colors: 'rgba(255,255,255,0.5)', fontSize: '12px' } } },
    grid: { borderColor: 'rgba(255,255,255,0.04)', strokeDashArray: 4, xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } } },
    colors: props.analytics.by_strategy.map(s => s.total_pnl >= 0 ? '#00C896' : '#FF4B4B'),
    tooltip: { theme: 'dark', x: { formatter: (v: string) => v }, y: { formatter: (v: number) => `$${v.toLocaleString()}` } },
}))

const strategySeries = computed(() => [{
    name: 'PnL',
    data: props.analytics.by_strategy.map(s => ({ x: s.strategy_name, y: s.total_pnl })),
}])

// ── Day of week ─────────────────────────────────────────────────
const dowOptions = computed(() => ({
    chart: { type: 'bar', toolbar: { show: false }, background: 'transparent', fontFamily: 'DM Sans, sans-serif' },
    plotOptions: { bar: { borderRadius: 6, columnWidth: '55%' } },
    dataLabels: { enabled: false },
    xaxis: {
        categories: props.analytics.by_day_of_week.map(d => d.label),
        labels: { style: { colors: 'rgba(255,255,255,0.35)', fontSize: '11px' } },
        axisBorder: { show: false }, axisTicks: { show: false },
    },
    yaxis: { labels: { style: { colors: 'rgba(255,255,255,0.3)', fontSize: '11px' }, formatter: (v: number) => `$${v}` } },
    grid: { borderColor: 'rgba(255,255,255,0.04)', strokeDashArray: 4 },
    colors: props.analytics.by_day_of_week.map(d => d.total_pnl >= 0 ? '#00C896' : '#FF4B4B'),
    tooltip: { theme: 'dark', y: { formatter: (v: number) => `$${v.toLocaleString()}` } },
}))

const dowSeries = computed(() => [{
    name: 'PnL',
    data: props.analytics.by_day_of_week.map(d => d.total_pnl),
}])

// ── Long vs Short ───────────────────────────────────────────────
const lvsOptions = computed(() => ({
    chart: { type: 'bar', toolbar: { show: false }, background: 'transparent', fontFamily: 'DM Sans, sans-serif' },
    plotOptions: { bar: { borderRadius: 6, columnWidth: '50%', distributed: true } },
    dataLabels: { enabled: false },
    xaxis: {
        categories: props.analytics.long_vs_short.map(d => d.direction.charAt(0).toUpperCase() + d.direction.slice(1)),
        labels: { style: { colors: 'rgba(255,255,255,0.35)', fontSize: '12px' } },
        axisBorder: { show: false }, axisTicks: { show: false },
    },
    yaxis: { labels: { style: { colors: 'rgba(255,255,255,0.3)', fontSize: '11px' } } },
    grid: { borderColor: 'rgba(255,255,255,0.04)' },
    colors: ['#00C896', '#FF6B8A'],
    legend: { show: false },
    tooltip: { theme: 'dark' },
}))

const lvsSeries = computed(() => [{ name: 'PnL', data: props.analytics.long_vs_short.map(d => d.total_pnl) }])

// ── RR distribution ─────────────────────────────────────────────
const rrOptions = computed(() => ({
    chart: { type: 'bar', toolbar: { show: false }, background: 'transparent', fontFamily: 'DM Sans, sans-serif' },
    plotOptions: { bar: { borderRadius: 5, columnWidth: '65%', distributed: true } },
    dataLabels: { enabled: false },
    xaxis: {
        categories: props.analytics.rr_distribution.map(r => r.range),
        labels: { style: { colors: 'rgba(255,255,255,0.35)', fontSize: '11px' } },
        axisBorder: { show: false }, axisTicks: { show: false },
    },
    yaxis: { labels: { style: { colors: 'rgba(255,255,255,0.3)', fontSize: '11px' } }, title: { text: 'Trades', style: { color: 'rgba(255,255,255,0.2)' } } },
    grid: { borderColor: 'rgba(255,255,255,0.04)', strokeDashArray: 4 },
    colors: ['#00E0AA', '#00C896', '#7BF5C0', '#FFB84D', '#FF4B4B'],
    legend: { show: false },
    tooltip: { theme: 'dark', y: { formatter: (v: number) => `${v} trades` } },
}))

const rrSeries = computed(() => [{ name: 'Trades', data: props.analytics.rr_distribution.map(r => r.count) }])

// ── Session radar ───────────────────────────────────────────────
const sessionRadarOptions = computed(() => ({
    chart: { type: 'radar', toolbar: { show: false }, background: 'transparent', fontFamily: 'DM Sans, sans-serif' },
    xaxis: { categories: props.analytics.by_session.map(s => s.label) },
    fill: { opacity: 0.15, colors: ['#00C896'] },
    stroke: { width: 2, colors: ['#00C896'] },
    markers: { size: 4, colors: ['#00C896'] },
    yaxis: { show: false },
    plotOptions: { radar: { polygons: { strokeColors: 'rgba(255,255,255,0.06)', fill: { colors: ['transparent'] } } } },
    tooltip: { theme: 'dark' },
}))

const sessionRadarSeries = computed(() => [{
    name: 'Win Rate',
    data: props.analytics.by_session.map(s => Math.round(s.win_rate)),
}])
</script>

<template>
    <Head title="Analytics" />
    <AppLayout>
        <!-- Key metrics row -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
            <StatCard label="Profit Factor"  :value="analytics.stats.profit_factor?.toFixed(2)"   color="#FFB84D" />
            <StatCard label="Expectancy"     :value="`$${analytics.stats.expectancy?.toFixed(0)}`" color="#00C896" />
            <StatCard label="Max Drawdown"   :value="`-$${Math.abs(analytics.stats.max_drawdown ?? 0).toFixed(0)}`" color="#FF4B4B" />
            <StatCard label="Gem. Houd tijd" :value="'—'" color="#7B9FFF" />
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mb-4">
            <!-- Strategy performance -->
            <div class="card p-5">
                <h3 class="text-sm font-semibold mb-4">Strategie Performance</h3>
                <VueApexCharts
                    v-if="analytics.by_strategy.length"
                    type="bar"
                    :options="strategyOptions"
                    :series="strategySeries"
                    height="220"
                />
                <div v-else class="empty-state h-48"><p class="text-text-muted text-sm">Geen data</p></div>
            </div>

            <!-- Day of week -->
            <div class="card p-5">
                <h3 class="text-sm font-semibold mb-4">Dag van de Week</h3>
                <VueApexCharts
                    v-if="analytics.by_day_of_week.length"
                    type="bar"
                    :options="dowOptions"
                    :series="dowSeries"
                    height="220"
                />
            </div>

            <!-- RR distribution -->
            <div class="card p-5">
                <h3 class="text-sm font-semibold mb-4">RR Verdeling</h3>
                <VueApexCharts
                    v-if="analytics.rr_distribution.length"
                    type="bar"
                    :options="rrOptions"
                    :series="rrSeries"
                    height="220"
                />
            </div>

            <!-- Long vs Short -->
            <div class="card p-5">
                <h3 class="text-sm font-semibold mb-4">Long vs Short</h3>
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div
                        v-for="d in analytics.long_vs_short"
                        :key="d.direction"
                        class="bg-panel rounded-xl p-3"
                    >
                        <p class="stat-label capitalize">{{ d.direction }}</p>
                        <p class="text-base font-bold num" :class="d.total_pnl >= 0 ? 'text-profit' : 'text-loss'">
                            {{ d.total_pnl >= 0 ? '+$' : '-$' }}{{ Math.abs(d.total_pnl).toLocaleString() }}
                        </p>
                        <p class="text-xs text-text-tertiary mt-1">{{ d.win_rate?.toFixed(0) }}% WR · {{ d.trades }} trades</p>
                    </div>
                </div>
                <VueApexCharts
                    v-if="analytics.long_vs_short.length"
                    type="bar"
                    :options="lvsOptions"
                    :series="lvsSeries"
                    height="140"
                />
            </div>
        </div>

        <!-- Strategy table -->
        <div class="card overflow-hidden">
            <div class="px-5 py-4 border-b border-panel-border">
                <h3 class="text-sm font-semibold">Strategie Overzicht</h3>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="border-b border-panel-border">
                        <th class="table-head-cell">Strategie</th>
                        <th class="table-head-cell text-right">Trades</th>
                        <th class="table-head-cell text-right">Win Rate</th>
                        <th class="table-head-cell text-right">Gem. RR</th>
                        <th class="table-head-cell text-right">PF</th>
                        <th class="table-head-cell text-right">Totale PnL</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="s in analytics.by_strategy"
                        :key="s.strategy_id ?? 'none'"
                        class="table-row"
                    >
                        <td class="table-cell font-semibold">{{ s.strategy_name }}</td>
                        <td class="table-cell text-right text-text-secondary">{{ s.trades }}</td>
                        <td class="table-cell text-right">
                            <div class="flex items-center justify-end gap-2">
                                <div class="w-20 progress-track hidden lg:block">
                                    <div
                                        class="progress-fill"
                                        :class="s.win_rate >= 50 ? 'bg-brand' : 'bg-loss'"
                                        :style="{ width: `${s.win_rate}%` }"
                                    />
                                </div>
                                <span :class="s.win_rate >= 50 ? 'text-profit' : 'text-loss'">{{ s.win_rate?.toFixed(0) }}%</span>
                            </div>
                        </td>
                        <td class="table-cell text-right num" :class="s.avg_rr >= 1 ? 'text-profit-text' : 'text-text-secondary'">
                            {{ s.avg_rr >= 0 ? '+' : '' }}{{ s.avg_rr?.toFixed(2) }}R
                        </td>
                        <td class="table-cell text-right num" :class="s.profit_factor >= 1.5 ? 'text-profit-text' : 'text-text-secondary'">
                            {{ s.profit_factor?.toFixed(2) }}
                        </td>
                        <td class="table-cell text-right num font-bold" :class="s.total_pnl >= 0 ? 'text-profit' : 'text-loss'">
                            {{ s.total_pnl >= 0 ? '+$' : '-$' }}{{ Math.abs(s.total_pnl).toLocaleString() }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
