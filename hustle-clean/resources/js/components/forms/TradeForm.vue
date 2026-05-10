<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useUiStore } from '@/stores/ui'
import type { Trade, Strategy, Account } from '@/types'

const props = defineProps<{
    trade?: Trade
    accounts: Account[]
    strategies: Strategy[]
}>()

const emit = defineEmits<{
    saved: []
    cancelled: []
}>()

const ui = useUiStore()
const isEdit = computed(() => !!props.trade)

const form = useForm({
    account_id:        props.trade?.account_id ?? (props.accounts[0]?.id ?? ''),
    ticker:            props.trade?.ticker ?? '',
    direction:         props.trade?.direction ?? 'long',
    status:            props.trade?.status ?? 'closed',
    entry_price:       props.trade?.entry_price ?? '',
    exit_price:        props.trade?.exit_price ?? '',
    stop_loss:         props.trade?.stop_loss ?? '',
    take_profit:       props.trade?.take_profit ?? '',
    position_size:     props.trade?.position_size ?? '',
    commission:        props.trade?.commission ?? '',
    risk_pct:          props.trade?.risk_pct ?? '',
    session:           props.trade?.session ?? 'new_york',
    strategy_id:       props.trade?.strategy_id ?? '',
    opened_at:         props.trade?.opened_at?.slice(0,16) ?? new Date().toISOString().slice(0,16),
    closed_at:         props.trade?.closed_at?.slice(0,16) ?? '',
    psychology_rating: props.trade?.psychology_rating ?? 7,
    psychology_notes:  props.trade?.psychology_notes ?? '',
    mistakes:          props.trade?.mistakes ?? [] as string[],
    notes:             props.trade?.notes ?? '',
    tags:              props.trade?.tags?.map(t => t.id) ?? [] as number[],
})

// Computed PnL preview
const pnlPreview = computed(() => {
    const entry = parseFloat(String(form.entry_price))
    const exit  = parseFloat(String(form.exit_price))
    const size  = parseFloat(String(form.position_size))
    const comm  = parseFloat(String(form.commission)) || 0
    if (!entry || !exit || !size) return null
    const raw = form.direction === 'long'
        ? (exit - entry) * size
        : (entry - exit) * size
    return raw - comm
})

const rrPreview = computed(() => {
    const entry = parseFloat(String(form.entry_price))
    const exit  = parseFloat(String(form.exit_price))
    const sl    = parseFloat(String(form.stop_loss))
    if (!entry || !exit || !sl) return null
    const risk   = Math.abs(entry - sl)
    const reward = form.direction === 'long' ? exit - entry : entry - exit
    return risk > 0 ? reward / risk : null
})

const sessions = [
    { value: 'asian',      label: 'Asian' },
    { value: 'london',     label: 'London' },
    { value: 'new_york',   label: 'New York' },
    { value: 'overnight',  label: 'Overnight' },
    { value: 'pre_market', label: 'Pre-Market' },
]

const mistakeOptions = [
    'FOMO entry', 'Moved SL', 'Oversized', 'No setup', 'Revenge trade',
    'Early exit', 'Late entry', 'Ignored plan', 'Overtrading',
]

function toggleMistake(m: string) {
    const idx = form.mistakes.indexOf(m)
    if (idx === -1) form.mistakes.push(m)
    else form.mistakes.splice(idx, 1)
}

function submit() {
    if (isEdit.value) {
        form.put(route('trades.update', props.trade!.id), {
            onSuccess: () => { ui.toast('Trade bijgewerkt', 'success'); emit('saved') },
        })
    } else {
        form.post(route('trades.store'), {
            onSuccess: () => { ui.toast('Trade gelogd', 'success'); emit('saved') },
        })
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-5">
        <!-- Row 1: account + ticker + direction -->
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="label">Account</label>
                <select v-model="form.account_id" class="input-select">
                    <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.name }}</option>
                </select>
                <p v-if="form.errors.account_id" class="mt-1 text-xs text-loss">{{ form.errors.account_id }}</p>
            </div>
            <div>
                <label class="label">Instrument</label>
                <input v-model="form.ticker" class="input uppercase" placeholder="NQ, ES, GC..." required />
                <p v-if="form.errors.ticker" class="mt-1 text-xs text-loss">{{ form.errors.ticker }}</p>
            </div>
            <div>
                <label class="label">Richting</label>
                <div class="flex gap-2">
                    <button
                        type="button"
                        :class="['flex-1 btn btn-sm', form.direction === 'long' ? 'bg-profit-dim border border-profit/30 text-profit-text' : 'btn-secondary']"
                        @click="form.direction = 'long'"
                    >Long ↑</button>
                    <button
                        type="button"
                        :class="['flex-1 btn btn-sm', form.direction === 'short' ? 'bg-loss-dim border border-loss/30 text-loss-text' : 'btn-secondary']"
                        @click="form.direction = 'short'"
                    >Short ↓</button>
                </div>
            </div>
        </div>

        <!-- Row 2: prices -->
        <div class="grid grid-cols-4 gap-4">
            <div>
                <label class="label">Entry Prijs</label>
                <input v-model="form.entry_price" type="number" step="any" class="input num" placeholder="21340" required />
            </div>
            <div>
                <label class="label">Exit Prijs</label>
                <input v-model="form.exit_price" type="number" step="any" class="input num" placeholder="21520" />
            </div>
            <div>
                <label class="label">Stop Loss</label>
                <input v-model="form.stop_loss" type="number" step="any" class="input num" placeholder="21280" />
            </div>
            <div>
                <label class="label">Take Profit</label>
                <input v-model="form.take_profit" type="number" step="any" class="input num" placeholder="21600" />
            </div>
        </div>

        <!-- Row 3: size + commission + risk -->
        <div class="grid grid-cols-4 gap-4">
            <div>
                <label class="label">Positiegrootte</label>
                <input v-model="form.position_size" type="number" step="any" min="0" class="input num" placeholder="1" required />
            </div>
            <div>
                <label class="label">Commissie ($)</label>
                <input v-model="form.commission" type="number" step="0.01" min="0" class="input num" placeholder="4.50" />
            </div>
            <div>
                <label class="label">Risico %</label>
                <input v-model="form.risk_pct" type="number" step="0.01" min="0" max="100" class="input num" placeholder="1.0" />
            </div>
            <div>
                <label class="label">Sessie</label>
                <select v-model="form.session" class="input-select">
                    <option v-for="s in sessions" :key="s.value" :value="s.value">{{ s.label }}</option>
                </select>
            </div>
        </div>

        <!-- Row 4: timestamps + strategy -->
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="label">Geopend op</label>
                <input v-model="form.opened_at" type="datetime-local" class="input" />
            </div>
            <div>
                <label class="label">Gesloten op</label>
                <input v-model="form.closed_at" type="datetime-local" class="input" />
            </div>
            <div>
                <label class="label">Strategie</label>
                <select v-model="form.strategy_id" class="input-select">
                    <option value="">Geen strategie</option>
                    <option v-for="s in strategies" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
            </div>
        </div>

        <!-- PnL preview bar -->
        <div
            v-if="pnlPreview !== null"
            :class="['flex items-center justify-between px-4 py-3 rounded-xl text-sm font-semibold', pnlPreview >= 0 ? 'bg-profit-dim border border-profit/20' : 'bg-loss-dim border border-loss/20']"
        >
            <span class="text-text-secondary">Berekende PnL</span>
            <div class="flex items-center gap-4">
                <span :class="pnlPreview >= 0 ? 'text-profit-text' : 'text-loss-text'">
                    {{ pnlPreview >= 0 ? '+' : '' }}${{ pnlPreview.toFixed(2) }}
                </span>
                <span v-if="rrPreview !== null" class="text-text-tertiary text-xs">
                    RR: {{ rrPreview >= 0 ? '+' : '' }}{{ rrPreview.toFixed(2) }}R
                </span>
            </div>
        </div>

        <!-- Psychology -->
        <div>
            <label class="label">Psychologie Score ({{ form.psychology_rating }}/10)</label>
            <div class="flex items-center gap-1 mt-2">
                <button
                    v-for="n in 10"
                    :key="n"
                    type="button"
                    :class="['w-8 h-8 rounded-lg text-xs font-bold transition-all duration-100', n <= form.psychology_rating ? 'bg-brand text-surface shadow-glow-sm' : 'bg-panel-active text-text-muted hover:bg-panel-hover']"
                    @click="form.psychology_rating = n"
                >{{ n }}</button>
            </div>
        </div>

        <!-- Mistakes -->
        <div>
            <label class="label">Fouten</label>
            <div class="flex flex-wrap gap-2 mt-1">
                <button
                    v-for="m in mistakeOptions"
                    :key="m"
                    type="button"
                    :class="['tag cursor-pointer transition-all duration-100', form.mistakes.includes(m) ? 'tag-loss' : 'tag-neutral hover:border-loss/30']"
                    @click="toggleMistake(m)"
                >{{ m }}</button>
            </div>
        </div>

        <!-- Notes -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="label">Psychologie notities</label>
                <textarea v-model="form.psychology_notes" class="textarea" rows="3" placeholder="Hoe voelde je je tijdens deze trade?" />
            </div>
            <div>
                <label class="label">Trade notities</label>
                <textarea v-model="form.notes" class="textarea" rows="3" placeholder="Setup beschrijving, marktverhaal..." />
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="button" class="btn btn-secondary" @click="$emit('cancelled')">Annuleren</button>
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                <span v-if="form.processing" class="animate-spin inline-block w-3.5 h-3.5 border-2 border-surface border-t-transparent rounded-full" />
                {{ isEdit ? 'Trade opslaan' : 'Trade loggen' }}
            </button>
        </div>
    </form>
</template>
