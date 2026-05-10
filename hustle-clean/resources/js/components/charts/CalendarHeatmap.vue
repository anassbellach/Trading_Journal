<script setup lang="ts">
import { computed, ref } from 'vue'
import { format, startOfMonth, getDay, getDaysInMonth, parseISO } from 'date-fns'
import { nl } from 'date-fns/locale'
import type { CalendarDay } from '@/types'

const props = defineProps<{
    data: CalendarDay[]
    year: number
    month: number   // 1-12
    loading?: boolean
}>()

const emit = defineEmits<{
    dayClick: [day: CalendarDay]
    prevMonth: []
    nextMonth: []
}>()

const weekDays = ['Ma', 'Di', 'Wo', 'Do', 'Vr', 'Za', 'Zo']

const daysInMonth = computed(() => getDaysInMonth(new Date(props.year, props.month - 1)))

// offset: Monday = 0
const startOffset = computed(() => {
    const d = getDay(startOfMonth(new Date(props.year, props.month - 1)))
    return d === 0 ? 6 : d - 1
})

const dayMap = computed(() => {
    const m: Record<string, CalendarDay> = {}
    props.data.forEach(d => { m[d.date] = d })
    return m
})

function getDay2(day: number): CalendarDay | null {
    const dateStr = `${props.year}-${String(props.month).padStart(2, '0')}-${String(day).padStart(2, '0')}`
    return dayMap.value[dateStr] ?? null
}

function cellClass(day: CalendarDay | null) {
    if (!day || !day.is_trading_day) return 'bg-panel/50'
    if (day.pnl === null) return 'bg-panel'
    if (day.pnl > 1000)  return 'bg-profit/50 border border-profit/30'
    if (day.pnl > 0)     return 'bg-profit/20 border border-profit/15'
    if (day.pnl < -500)  return 'bg-loss/50 border border-loss/30'
    if (day.pnl < 0)     return 'bg-loss/20 border border-loss/15'
    return 'bg-panel'
}

const monthLabel = computed(() => {
    return format(new Date(props.year, props.month - 1), 'MMMM yyyy', { locale: nl })
})

const totalCells = computed(() => Math.ceil((startOffset.value + daysInMonth.value) / 7) * 7)
</script>

<template>
    <div class="card p-5">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-text-primary capitalize">{{ monthLabel }}</h3>
            <div class="flex gap-1.5">
                <button class="btn btn-ghost btn-xs" @click="$emit('prevMonth')">←</button>
                <button class="btn btn-ghost btn-xs" @click="$emit('nextMonth')">→</button>
            </div>
        </div>

        <!-- Skeleton -->
        <div v-if="loading" class="grid grid-cols-7 gap-1">
            <div v-for="i in 35" :key="i" class="skeleton rounded aspect-square" />
        </div>

        <template v-else>
            <!-- Weekday headers -->
            <div class="grid grid-cols-7 gap-1 mb-1">
                <div
                    v-for="d in weekDays"
                    :key="d"
                    class="text-center text-[10px] font-semibold text-text-muted py-1"
                >{{ d }}</div>
            </div>

            <!-- Days grid -->
            <div class="grid grid-cols-7 gap-1">
                <!-- Offset empty cells -->
                <div v-for="i in startOffset" :key="`e-${i}`" />

                <!-- Day cells -->
                <div
                    v-for="day in daysInMonth"
                    :key="day"
                    :class="['aspect-square rounded-lg flex flex-col items-center justify-center cursor-pointer transition-all duration-100 hover:ring-1 hover:ring-brand/30 relative', cellClass(getDay2(day))]"
                    @click="getDay2(day) && $emit('dayClick', getDay2(day)!)"
                >
                    <span class="text-[11px] font-medium text-text-secondary">{{ day }}</span>
                    <div
                        v-if="getDay2(day)?.is_trading_day"
                        class="absolute bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full"
                        :class="(getDay2(day)?.pnl ?? 0) >= 0 ? 'bg-profit' : 'bg-loss'"
                    />
                </div>

                <!-- Trailing empty -->
                <div v-for="i in (totalCells - startOffset - daysInMonth)" :key="`t-${i}`" />
            </div>

            <!-- Legend -->
            <div class="flex items-center gap-4 mt-4 pt-3 border-t border-panel-border">
                <div v-for="item in [
                    { cls: 'bg-profit/50', label: '+$1000+' },
                    { cls: 'bg-profit/20', label: 'Winstgevend' },
                    { cls: 'bg-loss/20',   label: 'Verliesgevend' },
                    { cls: 'bg-loss/50',   label: '-$500+' },
                ]" :key="item.label" class="flex items-center gap-1.5">
                    <div :class="['w-2.5 h-2.5 rounded-sm', item.cls]" />
                    <span class="text-xs text-text-muted">{{ item.label }}</span>
                </div>
            </div>
        </template>
    </div>
</template>
