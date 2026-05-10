<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/components/layout/AppLayout.vue'
import CalendarHeatmap from '@/components/charts/CalendarHeatmap.vue'
import type { CalendarDay, DashboardStats, Trade } from '@/types'

const props = defineProps<{
    calendarData: CalendarDay[]
    stats: DashboardStats
    year: number
    month: number
    dayTrades: Trade[]
    selectedDate: string | null
}>()

const selectedDay = ref<CalendarDay | null>(null)

function onDayClick(day: CalendarDay) {
    selectedDay.value = day
    router.get(route('calendar.index'), { year: props.year, month: props.month, date: day.date }, {
        preserveState: true, replace: true,
    })
}

function prevMonth() {
    const d = new Date(props.year, props.month - 2, 1)
    router.get(route('calendar.index'), { year: d.getFullYear(), month: d.getMonth() + 1 }, { preserveState: false })
}

function nextMonth() {
    const d = new Date(props.year, props.month, 1)
    router.get(route('calendar.index'), { year: d.getFullYear(), month: d.getMonth() + 1 }, { preserveState: false })
}

const monthPnl = computed(() => props.calendarData.reduce((s, d) => s + (d.pnl ?? 0), 0))
const tradingDays = computed(() => props.calendarData.filter(d => d.is_trading_day).length)
const winDays = computed(() => props.calendarData.filter(d => d.is_win_day === true).length)
const lossDays = computed(() => props.calendarData.filter(d => d.is_win_day === false).length)

function formatPnl(v: number) {
    return (v >= 0 ? '+$' : '-$') + Math.abs(v).toLocaleString('nl-NL', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
</script>

<template>
    <Head title="Calendar" />
    <AppLayout>
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
            <!-- Calendar -->
            <div class="xl:col-span-2">
                <CalendarHeatmap
                    :data="calendarData"
                    :year="year"
                    :month="month"
                    @day-click="onDayClick"
                    @prev-month="prevMonth"
                    @next-month="nextMonth"
                />
            </div>

            <!-- Sidebar stats -->
            <div class="space-y-4">
                <!-- Month summary -->
                <div class="card p-5">
                    <p class="stat-label mb-2">Maand PnL</p>
                    <p class="text-3xl font-black num" :class="monthPnl >= 0 ? 'text-profit' : 'text-loss'">
                        {{ formatPnl(monthPnl) }}
                    </p>
                    <div class="grid grid-cols-3 gap-2 mt-4">
                        <div class="bg-panel rounded-lg p-2.5 text-center">
                            <p class="text-xs text-text-muted">Dagen</p>
                            <p class="text-base font-bold">{{ tradingDays }}</p>
                        </div>
                        <div class="bg-panel rounded-lg p-2.5 text-center">
                            <p class="text-xs text-text-muted">Win</p>
                            <p class="text-base font-bold text-profit">{{ winDays }}</p>
                        </div>
                        <div class="bg-panel rounded-lg p-2.5 text-center">
                            <p class="text-xs text-text-muted">Verlies</p>
                            <p class="text-base font-bold text-loss">{{ lossDays }}</p>
                        </div>
                    </div>
                </div>

                <!-- Streak -->
                <div class="card p-5">
                    <p class="stat-label mb-1">Huidige Streak</p>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">{{ stats.streak_type === 'win' ? '🔥' : stats.streak_type === 'loss' ? '❄️' : '—' }}</span>
                        <div>
                            <p class="text-xl font-bold">{{ stats.current_streak }} {{ stats.streak_type === 'win' ? 'wins' : stats.streak_type === 'loss' ? 'losses' : '—' }}</p>
                            <p class="text-xs text-text-muted">op rij</p>
                        </div>
                    </div>
                </div>

                <!-- Selected day trades -->
                <div v-if="dayTrades.length > 0" class="card p-5">
                    <p class="stat-label mb-3">{{ selectedDate ?? 'Vandaag' }}</p>
                    <div class="space-y-2">
                        <div
                            v-for="t in dayTrades"
                            :key="t.id"
                            class="flex items-center justify-between py-2.5 px-3 bg-panel rounded-xl"
                        >
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm">{{ t.ticker }}</span>
                                    <span :class="['tag text-xs', t.direction === 'long' ? 'tag-profit' : 'tag-loss']">
                                        {{ t.direction }}
                                    </span>
                                </div>
                                <p class="text-xs text-text-muted mt-0.5">{{ t.strategy?.name ?? '—' }}</p>
                            </div>
                            <span class="font-bold num text-sm" :class="(t.pnl ?? 0) >= 0 ? 'text-profit' : 'text-loss'">
                                {{ (t.pnl ?? 0) >= 0 ? '+$' : '-$' }}{{ Math.abs(t.pnl ?? 0).toLocaleString() }}
                            </span>
                        </div>
                    </div>
                </div>

                <div v-else-if="selectedDate" class="card p-5">
                    <div class="empty-state py-8">
                        <p class="text-text-muted text-sm">Geen trades op {{ selectedDate }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
