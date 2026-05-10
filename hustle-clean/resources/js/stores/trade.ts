import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import type { Trade, TradeFilters, PaginatedResponse } from '@/types'

export const useTradeStore = defineStore('trade', () => {
    // Filters
    const filters = ref<TradeFilters>({
        search: '',
        direction: '',
        session: '',
        strategy_id: '',
        status: 'closed',
        date_from: '',
        date_to: '',
        sort_by: 'opened_at',
        sort_dir: 'desc',
        per_page: 25,
        page: 1,
    })

    const loading = ref(false)

    function applyFilters(newFilters: Partial<TradeFilters>) {
        filters.value = { ...filters.value, ...newFilters, page: 1 }
        router.get(route('journal.index'), filters.value as Record<string, unknown>, {
            preserveState: true,
            replace: true,
        })
    }

    function resetFilters() {
        filters.value = {
            search: '',
            direction: '',
            session: '',
            strategy_id: '',
            status: 'closed',
            date_from: '',
            date_to: '',
            sort_by: 'opened_at',
            sort_dir: 'desc',
            per_page: 25,
            page: 1,
        }
    }

    function setPage(page: number) {
        applyFilters({ page })
    }

    // Selected trade for detail modal
    const selectedTrade = ref<Trade | null>(null)

    function selectTrade(trade: Trade) {
        selectedTrade.value = trade
    }

    function clearSelectedTrade() {
        selectedTrade.value = null
    }

    return {
        filters,
        loading,
        applyFilters,
        resetFilters,
        setPage,
        selectedTrade,
        selectTrade,
        clearSelectedTrade,
    }
})
