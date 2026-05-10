<script setup lang="ts">
import { ref, watch } from 'vue'
import { Head } from '@inertiajs/vue3'
import { storeToRefs } from 'pinia'
import { useDebounceFn } from '@vueuse/core'
import { useUiStore } from '@/stores/ui'
import { useTradeStore } from '@/stores/trade'
import AppLayout from '@/components/layout/AppLayout.vue'
import TradeTable from '@/components/common/TradeTable.vue'
import Modal from '@/components/common/Modal.vue'
import TradeForm from '@/components/forms/TradeForm.vue'
import StatCard from '@/components/common/StatCard.vue'
import {
    MagnifyingGlassIcon,
    FunnelIcon,
    ArrowDownTrayIcon,
    PlusIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline'
import type { Trade, PaginatedResponse, Strategy, Account, DashboardStats } from '@/types'

const props = defineProps<{
    trades: PaginatedResponse<Trade>
    stats: DashboardStats
    strategies: Strategy[]
    accounts: Account[]
    filters: Record<string, unknown>
}>()

const ui         = useUiStore()
const tradeStore = useTradeStore()
const { activeModal, modalData } = storeToRefs(ui)

// Sync server-provided filters into store
tradeStore.filters = { ...tradeStore.filters, ...props.filters }

const localSearch = ref(String(props.filters.search ?? ''))
const showFilters = ref(false)

const debouncedSearch = useDebounceFn((val: string) => {
    tradeStore.applyFilters({ search: val })
}, 400)

watch(localSearch, debouncedSearch)

const SESSION_OPTIONS = [
    { value: '', label: 'Alle sessies' },
    { value: 'asian',      label: 'Asian' },
    { value: 'london',     label: 'London' },
    { value: 'new_york',   label: 'New York' },
    { value: 'overnight',  label: 'Overnight' },
    { value: 'pre_market', label: 'Pre-Market' },
]

const DIRECTION_OPTIONS = [
    { value: '',      label: 'Alle' },
    { value: 'long',  label: 'Long' },
    { value: 'short', label: 'Short' },
]

function exportCsv() {
    window.location.href = route('trades.export', tradeStore.filters as Record<string, unknown>)
}

const editTrade = ref<Trade | null>(null)
function openEdit(trade: Trade) {
    editTrade.value = trade
    ui.openModal('edit-trade')
}
</script>

<template>
    <Head title="Journal" />
    <AppLayout>
        <!-- Stats row -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-5">
            <StatCard label="Trades"     :value="stats.total_trades"                     color="#E8EAF0" />
            <StatCard label="Win Rate"   :value="`${stats.win_rate?.toFixed(1)}%`"       color="#7B9FFF" />
            <StatCard label="Totale PnL" :value="`${stats.total_pnl >= 0 ? '+$' : '-$'}${Math.abs(stats.total_pnl).toLocaleString()}`" :color="stats.total_pnl >= 0 ? '#00C896' : '#FF4B4B'" />
            <StatCard label="Gem. Win"   :value="`+$${stats.avg_win?.toFixed(0)}`"       color="#00C896" />
            <StatCard label="Gem. Verlies" :value="`-$${Math.abs(stats.avg_loss ?? 0).toFixed(0)}`" color="#FF4B4B" />
        </div>

        <!-- Filter bar -->
        <div class="flex flex-wrap items-center gap-2 mb-4">
            <!-- Search -->
            <div class="relative flex-1 min-w-[200px] max-w-xs">
                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-text-muted" />
                <input
                    v-model="localSearch"
                    class="input pl-8 text-sm"
                    placeholder="Zoek op ticker, strategie..."
                />
                <button
                    v-if="localSearch"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-text-muted hover:text-text-primary"
                    @click="localSearch = ''"
                >
                    <XMarkIcon class="h-3.5 w-3.5" />
                </button>
            </div>

            <!-- Direction quick filter -->
            <div class="flex gap-1">
                <button
                    v-for="opt in DIRECTION_OPTIONS"
                    :key="opt.value"
                    :class="['btn btn-sm', tradeStore.filters.direction === opt.value ? 'bg-brand-muted border border-brand-border text-brand' : 'btn-secondary']"
                    @click="tradeStore.applyFilters({ direction: opt.value as any })"
                >{{ opt.label }}</button>
            </div>

            <!-- Filter toggle -->
            <button
                :class="['btn btn-sm', showFilters ? 'btn-primary' : 'btn-secondary']"
                @click="showFilters = !showFilters"
            >
                <FunnelIcon class="h-3.5 w-3.5" />
                Filters
            </button>

            <!-- Export -->
            <button class="btn btn-secondary btn-sm ml-auto" @click="exportCsv">
                <ArrowDownTrayIcon class="h-3.5 w-3.5" />
                CSV Export
            </button>

            <!-- Add trade -->
            <button class="btn btn-primary btn-sm" @click="ui.openModal('add-trade')">
                <PlusIcon class="h-3.5 w-3.5" />
                Trade loggen
            </button>
        </div>

        <!-- Extended filters -->
        <Transition name="fade-up">
            <div v-if="showFilters" class="card p-4 mb-4 grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="label">Sessie</label>
                    <select
                        :value="tradeStore.filters.session"
                        class="input-select"
                        @change="tradeStore.applyFilters({ session: ($event.target as HTMLSelectElement).value as any })"
                    >
                        <option v-for="opt in SESSION_OPTIONS" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                    </select>
                </div>
                <div>
                    <label class="label">Strategie</label>
                    <select
                        :value="tradeStore.filters.strategy_id"
                        class="input-select"
                        @change="tradeStore.applyFilters({ strategy_id: ($event.target as HTMLSelectElement).value as any })"
                    >
                        <option value="">Alle strategieën</option>
                        <option v-for="s in strategies" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="label">Vanaf datum</label>
                    <input
                        type="date"
                        :value="tradeStore.filters.date_from"
                        class="input"
                        @change="tradeStore.applyFilters({ date_from: ($event.target as HTMLInputElement).value })"
                    />
                </div>
                <div>
                    <label class="label">Tot datum</label>
                    <input
                        type="date"
                        :value="tradeStore.filters.date_to"
                        class="input"
                        @change="tradeStore.applyFilters({ date_to: ($event.target as HTMLInputElement).value })"
                    />
                </div>
                <div class="col-span-full flex justify-end">
                    <button class="btn btn-ghost btn-sm text-text-tertiary" @click="tradeStore.resetFilters()">
                        Filters resetten
                    </button>
                </div>
            </div>
        </Transition>

        <!-- Table -->
        <TradeTable :trades="trades" />

        <!-- Add trade modal -->
        <Modal :show="activeModal === 'add-trade'" title="Trade Loggen" size="xl" @close="ui.closeModal">
            <TradeForm :accounts="accounts" :strategies="strategies" @saved="ui.closeModal" @cancelled="ui.closeModal" />
        </Modal>

        <!-- Edit trade modal -->
        <Modal :show="activeModal === 'edit-trade'" title="Trade Bewerken" size="xl" @close="ui.closeModal">
            <TradeForm
                v-if="editTrade"
                :trade="editTrade"
                :accounts="accounts"
                :strategies="strategies"
                @saved="ui.closeModal"
                @cancelled="ui.closeModal"
            />
        </Modal>

        <!-- Trade detail modal -->
        <Modal :show="activeModal === 'trade-detail'" title="Trade Detail" size="lg" @close="ui.closeModal">
            <TradeDetailPanel v-if="tradeStore.selectedTrade" :trade="tradeStore.selectedTrade" @edit="openEdit" />
        </Modal>
    </AppLayout>
</template>
