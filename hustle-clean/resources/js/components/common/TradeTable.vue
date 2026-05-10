<script setup lang="ts">
import { computed } from 'vue'
import { useTradeStore } from '@/stores/trade'
import { useUiStore } from '@/stores/ui'
import { ChevronUpIcon, ChevronDownIcon } from '@heroicons/vue/24/outline'
import type { Trade, PaginatedResponse } from '@/types'

const props = defineProps<{
    trades: PaginatedResponse<Trade>
    loading?: boolean
}>()

const tradeStore = useTradeStore()
const ui = useUiStore()

const SESSION_LABELS: Record<string, string> = {
    asian:      'AS',
    london:     'EU',
    new_york:   'NY',
    overnight:  'ON',
    pre_market: 'PM',
}

function sortBy(col: string) {
    const current = tradeStore.filters.sort_by
    const dir = tradeStore.filters.sort_dir
    tradeStore.applyFilters({
        sort_by:  col,
        sort_dir: current === col && dir === 'desc' ? 'asc' : 'desc',
    })
}

function formatPnl(pnl: number | null) {
    if (pnl === null) return '—'
    return (pnl >= 0 ? '+$' : '-$') + Math.abs(pnl).toLocaleString('nl-NL', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatRr(rr: number | null) {
    if (rr === null) return '—'
    return (rr >= 0 ? '+' : '') + rr.toFixed(2) + 'R'
}

function formatDate(dt: string) {
    return new Date(dt).toLocaleDateString('nl-NL', { day: '2-digit', month: 'short', year: '2-digit' })
}

function formatDuration(secs: number | null) {
    if (!secs) return '—'
    const h = Math.floor(secs / 3600)
    const m = Math.floor((secs % 3600) / 60)
    return h > 0 ? `${h}h ${m}m` : `${m}m`
}

const columns = [
    { key: 'ticker',      label: 'Ticker',    sortable: true },
    { key: 'direction',   label: 'Dir.',      sortable: false },
    { key: 'opened_at',   label: 'Datum',     sortable: true },
    { key: 'entry_price', label: 'Entry',     sortable: false },
    { key: 'exit_price',  label: 'Exit',      sortable: false },
    { key: 'rr_ratio',    label: 'RR',        sortable: true },
    { key: 'pnl',         label: 'PnL',       sortable: true },
    { key: 'session',     label: 'Sessie',    sortable: false },
    { key: 'strategy',    label: 'Strategie', sortable: false },
    { key: 'duration',    label: 'Duur',      sortable: true },
]
</script>

<template>
    <div class="card overflow-hidden">
        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px]">
                <thead>
                    <tr class="border-b border-panel-border">
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            :class="['table-head-cell', col.sortable && 'cursor-pointer hover:text-text-primary transition-colors select-none']"
                            @click="col.sortable && sortBy(col.key)"
                        >
                            <div class="flex items-center gap-1">
                                {{ col.label }}
                                <template v-if="col.sortable && tradeStore.filters.sort_by === col.key">
                                    <ChevronUpIcon v-if="tradeStore.filters.sort_dir === 'asc'" class="h-3 w-3 text-brand" />
                                    <ChevronDownIcon v-else class="h-3 w-3 text-brand" />
                                </template>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Skeleton rows -->
                    <template v-if="loading">
                        <tr v-for="i in 8" :key="i" class="border-b border-panel-border/50">
                            <td v-for="j in columns.length" :key="j" class="table-cell">
                                <div class="skeleton h-3.5 rounded" :style="{ width: `${40 + Math.random() * 40}%` }" />
                            </td>
                        </tr>
                    </template>

                    <!-- Empty state -->
                    <tr v-else-if="!trades.data.length">
                        <td :colspan="columns.length" class="py-16">
                            <div class="empty-state">
                                <div class="w-12 h-12 rounded-2xl bg-panel flex items-center justify-center mb-3">
                                    <span class="text-xl">📋</span>
                                </div>
                                <p class="text-text-secondary font-medium mb-1">Geen trades gevonden</p>
                                <p class="text-text-muted text-xs">Pas de filters aan of log je eerste trade</p>
                            </div>
                        </td>
                    </tr>

                    <!-- Trade rows -->
                    <tr
                        v-for="trade in trades.data"
                        :key="trade.id"
                        class="table-row"
                        @click="tradeStore.selectTrade(trade); ui.openModal('trade-detail')"
                    >
                        <td class="table-cell">
                            <span class="font-bold text-text-primary tracking-wide">{{ trade.ticker }}</span>
                        </td>
                        <td class="table-cell">
                            <span :class="['tag text-xs', trade.direction === 'long' ? 'tag-profit' : 'tag-loss']">
                                {{ trade.direction === 'long' ? '↑ Long' : '↓ Short' }}
                            </span>
                        </td>
                        <td class="table-cell text-text-secondary text-xs">{{ formatDate(trade.opened_at) }}</td>
                        <td class="table-cell num text-text-secondary">{{ trade.entry_price }}</td>
                        <td class="table-cell num text-text-secondary">{{ trade.exit_price ?? '—' }}</td>
                        <td class="table-cell num font-semibold" :class="(trade.rr_ratio ?? 0) >= 0 ? 'text-profit-text' : 'text-loss-text'">
                            {{ formatRr(trade.rr_ratio) }}
                        </td>
                        <td class="table-cell num font-bold" :class="(trade.pnl ?? 0) >= 0 ? 'text-profit' : 'text-loss'">
                            {{ formatPnl(trade.pnl) }}
                        </td>
                        <td class="table-cell">
                            <span class="tag tag-blue text-xs">{{ SESSION_LABELS[trade.session] ?? trade.session }}</span>
                        </td>
                        <td class="table-cell text-text-secondary text-xs">{{ trade.strategy?.name ?? '—' }}</td>
                        <td class="table-cell text-text-tertiary text-xs">{{ formatDuration(trade.duration_seconds) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="trades.meta.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t border-panel-border">
            <p class="text-xs text-text-tertiary">
                {{ trades.meta.from }}–{{ trades.meta.to }} van {{ trades.meta.total }} trades
            </p>
            <div class="flex items-center gap-1">
                <button
                    :disabled="trades.meta.current_page === 1"
                    class="btn btn-ghost btn-xs disabled:opacity-30"
                    @click="tradeStore.setPage(trades.meta.current_page - 1)"
                >←</button>
                <template v-for="p in trades.meta.last_page" :key="p">
                    <button
                        v-if="Math.abs(p - trades.meta.current_page) <= 2 || p === 1 || p === trades.meta.last_page"
                        :class="['btn btn-xs', p === trades.meta.current_page ? 'btn-primary' : 'btn-ghost']"
                        @click="tradeStore.setPage(p)"
                    >{{ p }}</button>
                    <span
                        v-else-if="Math.abs(p - trades.meta.current_page) === 3"
                        class="px-1 text-text-muted text-xs"
                    >…</span>
                </template>
                <button
                    :disabled="trades.meta.current_page === trades.meta.last_page"
                    class="btn btn-ghost btn-xs disabled:opacity-30"
                    @click="tradeStore.setPage(trades.meta.current_page + 1)"
                >→</button>
            </div>
        </div>
    </div>
</template>
