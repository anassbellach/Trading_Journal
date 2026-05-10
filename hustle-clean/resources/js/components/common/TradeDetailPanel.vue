<script setup lang="ts">
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { useUiStore } from '@/stores/ui'
import { useFormatters } from '@/composables/useFormatters'
import { PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'
import type { Trade } from '@/types'

const props = defineProps<{ trade: Trade }>()
const emit  = defineEmits<{ edit: [trade: Trade] }>()

const ui   = useUiStore()
const fmt  = useFormatters()

const SESSION_LABELS: Record<string, string> = {
    asian: 'Asian', london: 'London', new_york: 'New York',
    overnight: 'Overnight', pre_market: 'Pre-Market',
}

function deleteTrade() {
    if (!confirm(`Trade ${props.trade.ticker} verwijderen?`)) return
    router.delete(route('trades.destroy', props.trade.id), {
        onSuccess: () => { ui.closeModal(); ui.toast('Trade verwijderd', 'success') },
    })
}

const stats = computed(() => [
    { label: 'PnL',         value: fmt.formatPnl(props.trade.pnl),                color: fmt.formatPnlColor(props.trade.pnl) },
    { label: 'RR',          value: fmt.formatRr(props.trade.rr_ratio),             color: (props.trade.rr_ratio ?? 0) >= 0 ? 'text-profit-text' : 'text-loss-text' },
    { label: 'Duur',        value: fmt.formatDuration(props.trade.duration_seconds), color: 'text-text-primary' },
    { label: 'Entry',       value: props.trade.entry_price?.toString() ?? '—',     color: 'text-text-primary' },
    { label: 'Exit',        value: props.trade.exit_price?.toString() ?? '—',      color: 'text-text-primary' },
    { label: 'Sessie',      value: SESSION_LABELS[props.trade.session] ?? props.trade.session, color: 'text-accent-blue' },
    { label: 'Stop Loss',   value: props.trade.stop_loss?.toString() ?? '—',       color: 'text-loss-text' },
    { label: 'Take Profit', value: props.trade.take_profit?.toString() ?? '—',     color: 'text-profit-text' },
])
</script>

<template>
    <div class="space-y-5">
        <!-- Header row -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-bold">{{ trade.ticker }}</h2>
                <span :class="['tag', trade.direction === 'long' ? 'tag-profit' : 'tag-loss']">
                    {{ trade.direction === 'long' ? '↑ Long' : '↓ Short' }}
                </span>
                <span :class="['tag', trade.is_win ? 'tag-profit' : 'tag-loss']">
                    {{ trade.is_win ? 'WIN' : 'VERLIES' }}
                </span>
            </div>
            <div class="flex gap-2">
                <button class="btn btn-secondary btn-sm" @click="$emit('edit', trade)">
                    <PencilIcon class="h-3.5 w-3.5" /> Bewerken
                </button>
                <button class="btn btn-danger btn-sm" @click="deleteTrade">
                    <TrashIcon class="h-3.5 w-3.5" />
                </button>
            </div>
        </div>

        <!-- Stats grid -->
        <div class="grid grid-cols-4 gap-3">
            <div v-for="s in stats" :key="s.label" class="bg-panel rounded-xl p-3">
                <p class="stat-label">{{ s.label }}</p>
                <p :class="['text-base font-bold num', s.color]">{{ s.value }}</p>
            </div>
        </div>

        <!-- Strategy + account -->
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-panel rounded-xl p-3">
                <p class="stat-label">Strategie</p>
                <p class="text-sm font-semibold">{{ trade.strategy?.name ?? 'Geen' }}</p>
            </div>
            <div class="bg-panel rounded-xl p-3">
                <p class="stat-label">Account</p>
                <p class="text-sm font-semibold">{{ trade.account?.name ?? '—' }}</p>
            </div>
        </div>

        <!-- Psychology rating -->
        <div class="bg-panel rounded-xl p-4">
            <p class="stat-label mb-2">Psychologie Score</p>
            <div class="flex items-center gap-1.5">
                <div
                    v-for="n in 10"
                    :key="n"
                    :class="['w-7 h-7 rounded-lg text-xs font-bold flex items-center justify-center transition-colors', n <= (trade.psychology_rating ?? 0) ? 'bg-brand text-surface' : 'bg-panel-active text-text-muted']"
                >{{ n }}</div>
                <span class="ml-2 text-sm font-semibold text-brand">{{ trade.psychology_rating ?? '—' }}/10</span>
            </div>
        </div>

        <!-- Mistakes -->
        <div v-if="trade.mistakes?.length" class="bg-panel rounded-xl p-4">
            <p class="stat-label mb-2">Fouten</p>
            <div class="flex flex-wrap gap-1.5">
                <span v-for="m in trade.mistakes" :key="m" class="tag tag-loss text-xs">{{ m }}</span>
            </div>
        </div>

        <!-- Notes -->
        <div v-if="trade.notes || trade.psychology_notes" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div v-if="trade.psychology_notes" class="bg-panel rounded-xl p-4">
                <p class="stat-label mb-1">Psychologie notities</p>
                <p class="text-xs text-text-secondary leading-relaxed">{{ trade.psychology_notes }}</p>
            </div>
            <div v-if="trade.notes" class="bg-panel rounded-xl p-4">
                <p class="stat-label mb-1">Trade notities</p>
                <p class="text-xs text-text-secondary leading-relaxed">{{ trade.notes }}</p>
            </div>
        </div>

        <!-- Screenshots -->
        <div v-if="trade.screenshots?.length" class="bg-panel rounded-xl p-4">
            <p class="stat-label mb-3">Screenshots ({{ trade.screenshots.length }})</p>
            <div class="grid grid-cols-2 gap-3">
                <a
                    v-for="ss in trade.screenshots"
                    :key="ss.id"
                    :href="ss.url"
                    target="_blank"
                    class="block rounded-xl overflow-hidden border border-panel-border hover:border-brand/40 transition-colors"
                >
                    <img :src="ss.url" :alt="`Screenshot ${ss.type}`" class="w-full h-32 object-cover" />
                </a>
            </div>
        </div>

        <!-- Tags -->
        <div v-if="trade.tags?.length" class="flex flex-wrap gap-1.5">
            <span v-for="tag in trade.tags" :key="tag.id" class="tag tag-blue text-xs">{{ tag.name }}</span>
        </div>

        <!-- Footer meta -->
        <div class="flex items-center justify-between text-xs text-text-muted pt-2 border-t border-panel-border">
            <span>Geopend: {{ new Date(trade.opened_at).toLocaleString('nl-NL') }}</span>
            <span v-if="trade.closed_at">Gesloten: {{ new Date(trade.closed_at).toLocaleString('nl-NL') }}</span>
        </div>
    </div>
</template>
