<script setup lang="ts">
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import type { AiInsight } from '@/types'

const props = defineProps<{
    insight: AiInsight
}>()

const emit = defineEmits<{
    markRead: [id: number]
}>()

const cardClass = computed(() => ({
    warning:  'insight-warning',
    critical: 'insight-danger',
    info:     'insight-info',
    positive: 'insight-success',
}[props.insight.severity] ?? 'card'))

const iconBgClass = computed(() => ({
    warning:  'bg-accent-amber/15 text-accent-amber',
    critical: 'bg-loss/15 text-loss-text',
    info:     'bg-accent-blue/15 text-accent-blue',
    positive: 'bg-brand-muted text-brand',
}[props.insight.severity] ?? ''))

const tagClass = computed(() => ({
    warning:  'tag-amber',
    critical: 'tag-loss',
    info:     'tag-blue',
    positive: 'tag-profit',
}[props.insight.severity] ?? 'tag-neutral'))

const CATEGORY_ICONS: Record<string, string> = {
    revenge_trading: '⚡',
    overtrading:     '📊',
    best_edge:       '↑',
    risk_alert:      '⚠',
    weekly_summary:  '📅',
    pattern:         '◎',
}

const icon = computed(() => CATEGORY_ICONS[props.insight.category] ?? '✦')

function markRead() {
    if (!props.insight.is_read) {
        router.post(route('ai-insights.read', props.insight.id), {}, { preserveState: true })
        emit('markRead', props.insight.id)
    }
}
</script>

<template>
    <div
        :class="['p-4 rounded-2xl transition-all duration-200', cardClass, !insight.is_read && 'ring-1 ring-inset ring-white/5']"
        @click="markRead"
    >
        <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-2.5">
                <div :class="['w-8 h-8 rounded-xl flex items-center justify-center text-sm font-bold flex-shrink-0', iconBgClass]">
                    {{ icon }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-semibold text-text-primary">{{ insight.title }}</p>
                        <span v-if="!insight.is_read" class="w-1.5 h-1.5 rounded-full bg-brand flex-shrink-0" />
                    </div>
                </div>
            </div>
            <span :class="['tag text-[10px] flex-shrink-0', tagClass]">{{ insight.category.replace('_', ' ') }}</span>
        </div>

        <p class="text-sm text-text-secondary leading-relaxed mb-3">{{ insight.description }}</p>

        <ul v-if="insight.action_items.length" class="space-y-1.5">
            <li
                v-for="(action, i) in insight.action_items"
                :key="i"
                class="flex items-start gap-2 text-xs text-text-tertiary"
            >
                <span class="text-brand mt-0.5 flex-shrink-0">→</span>
                {{ action }}
            </li>
        </ul>

        <p class="mt-3 text-xs text-text-muted">
            {{ new Date(insight.generated_at).toLocaleDateString('nl-NL', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) }}
        </p>
    </div>
</template>
