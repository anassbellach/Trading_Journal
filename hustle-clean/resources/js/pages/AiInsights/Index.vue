<script setup lang="ts">
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/components/layout/AppLayout.vue'
import InsightCard from '@/components/common/InsightCard.vue'
import type { AiInsight } from '@/types'
import { SparklesIcon, ArrowPathIcon } from '@heroicons/vue/24/outline'

const props = defineProps<{
    insights: AiInsight[]
    weeklySummary: string | null
    lastGeneratedAt: string | null
    unread: number
}>()

const generating = ref(false)
const activeFilter = ref<string>('all')

const FILTERS = [
    { value: 'all',     label: 'Alle' },
    { value: 'warning', label: 'Waarschuwingen' },
    { value: 'positive',label: 'Positief' },
    { value: 'info',    label: 'Info' },
]

const filteredInsights = computed(() => {
    if (activeFilter.value === 'all') return props.insights
    return props.insights.filter(i => i.severity === activeFilter.value)
})

function generateInsights() {
    generating.value = true
    router.post(route('ai-insights.generate'), {}, {
        onFinish: () => { generating.value = false },
    })
}

function markAllRead() {
    router.post(route('ai-insights.read-all'), {}, { preserveState: true })
}

import { computed } from 'vue'
</script>

<template>
    <Head title="AI Inzichten" />
    <AppLayout>
        <!-- AI header -->
        <div class="card-glow p-5 mb-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-brand to-accent-purple flex items-center justify-center text-xl flex-shrink-0 shadow-glow-brand">
                ✦
            </div>
            <div class="flex-1">
                <h2 class="text-base font-bold">Hustle AI Coach</h2>
                <p class="text-sm text-text-secondary mt-0.5">
                    {{ insights.length }} inzichten geanalyseerd
                    <template v-if="lastGeneratedAt">
                        · Bijgewerkt {{ new Date(lastGeneratedAt).toLocaleTimeString('nl-NL', { hour: '2-digit', minute: '2-digit' }) }}
                    </template>
                </p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <span v-if="unread > 0" class="tag tag-profit">{{ unread }} nieuw</span>
                <button class="btn btn-secondary btn-sm" @click="markAllRead">Alles gelezen</button>
                <button
                    :class="['btn btn-primary btn-sm', generating && 'opacity-75']"
                    :disabled="generating"
                    @click="generateInsights"
                >
                    <ArrowPathIcon class="h-3.5 w-3.5" :class="generating && 'animate-spin'" />
                    {{ generating ? 'Analyseren...' : 'Vernieuwen' }}
                </button>
            </div>
        </div>

        <!-- Weekly summary -->
        <div v-if="weeklySummary" class="card p-5 mb-5">
            <div class="flex items-center gap-2 mb-3">
                <SparklesIcon class="h-4 w-4 text-brand" />
                <h3 class="text-sm font-semibold">Wekelijkse Samenvatting</h3>
            </div>
            <div class="text-sm text-text-secondary leading-relaxed prose prose-sm prose-invert max-w-none" v-html="weeklySummary" />
        </div>

        <!-- Filter tabs -->
        <div class="flex gap-1.5 mb-4">
            <button
                v-for="f in FILTERS"
                :key="f.value"
                :class="['btn btn-sm', activeFilter === f.value ? 'bg-brand-muted border border-brand-border text-brand' : 'btn-secondary']"
                @click="activeFilter = f.value"
            >{{ f.label }}</button>
        </div>

        <!-- Insights grid -->
        <div v-if="filteredInsights.length" class="grid grid-cols-1 xl:grid-cols-2 gap-3">
            <InsightCard
                v-for="insight in filteredInsights"
                :key="insight.id"
                :insight="insight"
            />
        </div>

        <!-- Empty state -->
        <div v-else class="empty-state py-24">
            <div class="w-16 h-16 rounded-2xl bg-panel flex items-center justify-center mb-4">
                <SparklesIcon class="h-7 w-7 text-text-muted" />
            </div>
            <p class="text-text-secondary font-medium mb-2">Geen inzichten gevonden</p>
            <p class="text-text-muted text-sm mb-4">Log meer trades en genereer je eerste AI analyse</p>
            <button class="btn btn-primary" @click="generateInsights">
                <SparklesIcon class="h-4 w-4" />
                Analyseer mijn trades
            </button>
        </div>
    </AppLayout>
</template>
