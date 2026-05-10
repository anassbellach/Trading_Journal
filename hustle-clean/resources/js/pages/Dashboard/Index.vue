<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { storeToRefs } from 'pinia'
import { useUiStore } from '@/stores/ui'
import AppLayout from '@/components/layout/AppLayout.vue'
import StatCard from '@/components/common/StatCard.vue'
import EquityChart from '@/components/charts/EquityChart.vue'
import DonutChart from '@/components/charts/DonutChart.vue'
import InsightCard from '@/components/common/InsightCard.vue'
import TradeTable from '@/components/common/TradeTable.vue'
import Modal from '@/components/common/Modal.vue'
import TradeForm from '@/components/forms/TradeForm.vue'
import VueApexCharts from 'vue-apexcharts'
import type { DashboardStats, EquityCurvePoint, PerformanceBySession, Trade, PaginatedResponse, AiInsight, Account, Strategy } from '@/types'

const props = defineProps<{
    stats: DashboardStats
    equityCurve: EquityCurvePoint[]
    bySessions: PerformanceBySession[]
    recentTrades: PaginatedResponse<Trade>
    topInsights: AiInsight[]
    accounts: Account[]
    strategies: Strategy[]
}>()

const ui = useUiStore()
const { activeModal } = storeToRefs(ui)

// Session bar chart options
const sessionChartOptions = computed(() => ({
    chart: { type: 'bar', toolbar: { show: false }, background: 'transparent', fontFamily: 'DM Sans, sans-serif' },
    plotOptions: { bar: { borderRadius: 5, columnWidth: '55%', distributed: false } },
    dataLabels: { enabled: false },
    xaxis: {
        categories: props.bySessions.map(s => s.label),
        labels: { style: { colors: 'rgba(255,255,255,0.35)', fontSize: '11px' } },
        axisBorder: { show: false }, axisTicks: { show: false },
    },
    yaxis: {
        labels: { style: { colors: 'rgba(255,255,255,0.3)', fontSize: '11px' }, formatter: (v: number) => `$${v}` },
    },
    grid: { borderColor: 'rgba(255,255,255,0.04)', strokeDashArray: 4, yaxis: { lines: { show: true } }, xaxis: { lines: { show: false } } },
    colors: props.bySessions.map(s => s.total_pnl >= 0 ? '#00C896' : '#FF4B4B'),
    tooltip: { theme: 'dark', y: { formatter: (v: number) => `$${v.toLocaleString()}` } },
}))

const sessionSeries = computed(() => [{
    name: 'PnL',
    data: props.bySessions.map(s => s.total_pnl),
}])

// Day of week chart
const dowOptions = computed(() => ({
    chart: { type: 'bar', toolbar: { show: false }, background: 'transparent', fontFamily: 'DM Sans, sans-serif' },
    plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
    dataLabels: { enabled: false },
    xaxis: {
        categories: ['Ma', 'Di', 'Wo', 'Do', 'Vr'],
        labels: { style: { colors: 'rgba(255,255,255,0.35)', fontSize: '11px' } },
        axisBorder: { show: false }, axisTicks: { show: false },
    },
    yaxis: { labels: { style: { colors: 'rgba(255,255,255,0.3)', fontSize: '11px' }, formatter: (v: number) => `$${v}` } },
    grid: { borderColor: 'rgba(255,255,255,0.04)', strokeDashArray: 4 },
    fill: { type: 'gradient', gradient: { shade: 'dark', type: 'vertical', shadeIntensity: 0.3, gradientToColors: ['#00A878'], stops: [0, 100] } },
    colors: ['#00C896'],
    tooltip: { theme: 'dark' },
}))

function formatPnl(v: number) {
    return (v >= 0 ? '+$' : '-$') + Math.abs(v).toLocaleString('nl-NL', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const statCards = computed(() => [
    {
        label: 'Totale PnL',
        value: formatPnl(props.stats.total_pnl),
        delta: `${props.stats.total_pnl_pct >= 0 ? '+' : ''}${props.stats.total_pnl_pct?.toFixed(1)}%`,
        deltaPositive: props.stats.total_pnl >= 0,
        color: props.stats.total_pnl >= 0 ? '#00C896' : '#FF4B4B',
    },
    {
        label: 'Win Rate',
        value: `${props.stats.win_rate?.toFixed(1)}%`,
        color: '#7B9FFF',
        delta: `${props.stats.winning_trades}W / ${props.stats.losing_trades}L`,
        deltaPositive: true,
    },
    {
        label: 'Profit Factor',
        value: props.stats.profit_factor?.toFixed(2),
        color: '#FFB84D',
        delta: props.stats.profit_factor >= 1.5 ? '✓ Gezond' : '↓ Verbetering nodig',
        deltaPositive: props.stats.profit_factor >= 1.5,
    },
    {
        label: 'Gem. RR',
        value: `${props.stats.avg_rr >= 0 ? '+' : ''}${props.stats.avg_rr?.toFixed(2)}R`,
        color: '#FF6B8A',
        delta: `${props.stats.total_trades} trades`,
        deltaPositive: props.stats.avg_rr >= 1,
    },
])
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout>
        <!-- Stat cards -->
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
            <StatCard
                v-for="card in statCards"
                :key="card.label"
                v-bind="card"
            />
        </div>

        <!-- Equity + Donut -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-4">
            <div class="xl:col-span-2">
                <EquityChart :data="equityCurve" />
            </div>
            <DonutChart :wins="stats.winning_trades" :losses="stats.losing_trades" />
        </div>

        <!-- Sessions + PnL streak -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mb-4">
            <!-- Session performance -->
            <div class="card p-5">
                <h3 class="text-sm font-semibold mb-4">Sessie Performance</h3>
                <VueApexCharts
                    v-if="bySessions.length"
                    type="bar"
                    :options="sessionChartOptions"
                    :series="sessionSeries"
                    height="180"
                />
                <div v-else class="empty-state h-44">
                    <p class="text-text-muted text-sm">Geen data</p>
                </div>
            </div>

            <!-- Key metrics -->
            <div class="card p-5">
                <h3 class="text-sm font-semibold mb-4">Kernstatistieken</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div
                        v-for="m in [
                            { label: 'Best Trade',    value: formatPnl(stats.best_trade),   positive: true },
                            { label: 'Worst Trade',   value: formatPnl(stats.worst_trade),  positive: false },
                            { label: 'Max Drawdown',  value: formatPnl(stats.max_drawdown), positive: false },
                            { label: 'Expectancy',    value: formatPnl(stats.expectancy),   positive: stats.expectancy >= 0 },
                            { label: 'Streak',        value: `${stats.current_streak}× ${stats.streak_type ?? '—'}`, positive: stats.streak_type === 'win' },
                            { label: 'Commissies',    value: `$${stats.commission_paid?.toFixed(2)}`, positive: null },
                        ]"
                        :key="m.label"
                        class="bg-panel rounded-xl p-3"
                    >
                        <p class="stat-label">{{ m.label }}</p>
                        <p
                            class="text-base font-bold num"
                            :class="m.positive === null ? 'text-text-primary' : m.positive ? 'text-profit' : 'text-loss'"
                        >{{ m.value }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI insights preview -->
        <div v-if="topInsights.length" class="mb-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold">AI Inzichten</h3>
                <Link :href="route('ai-insights.index')" class="text-xs text-brand hover:underline">Alle inzichten →</Link>
            </div>
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-3">
                <InsightCard v-for="ins in topInsights.slice(0,2)" :key="ins.id" :insight="ins" />
            </div>
        </div>

        <!-- Recent trades -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold">Recente Trades</h3>
                <Link :href="route('journal.index')" class="text-xs text-brand hover:underline">Alle trades →</Link>
            </div>
            <TradeTable :trades="recentTrades" />
        </div>

        <!-- Add trade modal -->
        <Modal
            :show="activeModal === 'add-trade'"
            title="Trade Loggen"
            size="xl"
            @close="ui.closeModal"
        >
            <TradeForm
                :accounts="accounts"
                :strategies="strategies"
                @saved="ui.closeModal"
                @cancelled="ui.closeModal"
            />
        </Modal>
    </AppLayout>
</template>
