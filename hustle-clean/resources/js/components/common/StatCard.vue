<script setup lang="ts">
import { computed } from 'vue'
import VueApexCharts from 'vue-apexcharts'

interface Props {
    label: string
    value: string | number
    delta?: string
    deltaPositive?: boolean
    color?: string
    sparkData?: number[]
    prefix?: string
    suffix?: string
    loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    color: '#00C896',
    sparkData: () => [],
    loading: false,
})

const sparkOptions = computed(() => ({
    chart: {
        type: 'line',
        sparkline: { enabled: true },
        animations: { enabled: true, speed: 600 },
    },
    stroke: { curve: 'smooth', width: 2 },
    colors: [props.color],
    tooltip: { enabled: false },
}))

const displayValue = computed(() => {
    const v = props.value
    if (typeof v === 'number') {
        if (props.prefix) return `${props.prefix}${v.toLocaleString('nl-NL', { minimumFractionDigits: 0, maximumFractionDigits: 2 })}`
        if (props.suffix) return `${v.toLocaleString('nl-NL', { minimumFractionDigits: 0, maximumFractionDigits: 2 })}${props.suffix}`
        return v.toLocaleString('nl-NL', { minimumFractionDigits: 0, maximumFractionDigits: 2 })
    }
    return v
})
</script>

<template>
    <div class="card p-5 flex flex-col gap-3">
        <!-- Skeleton -->
        <template v-if="loading">
            <div class="skeleton h-3 w-24 rounded" />
            <div class="skeleton h-7 w-32 rounded" />
            <div class="skeleton h-3 w-20 rounded" />
        </template>

        <template v-else>
            <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0">
                    <p class="stat-label">{{ label }}</p>
                    <p class="stat-value" :style="{ color }">{{ displayValue }}</p>
                </div>

                <!-- Sparkline -->
                <VueApexCharts
                    v-if="sparkData.length > 1"
                    type="line"
                    :options="sparkOptions"
                    :series="[{ data: sparkData }]"
                    height="40"
                    width="80"
                    class="flex-shrink-0 -mt-1 -mr-1"
                />
            </div>

            <!-- Delta -->
            <div v-if="delta" class="flex items-center gap-1.5">
                <span
                    class="text-xs font-semibold"
                    :class="deltaPositive !== false ? 'text-profit' : 'text-loss'"
                >
                    {{ delta }}
                </span>
                <span class="text-xs text-text-muted">vs vorige periode</span>
            </div>
        </template>
    </div>
</template>
