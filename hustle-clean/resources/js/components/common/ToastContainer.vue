<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { useUiStore } from '@/stores/ui'
import {
    CheckCircleIcon,
    XCircleIcon,
    InformationCircleIcon,
    ExclamationTriangleIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline'

const ui = useUiStore()
const { toasts } = storeToRefs(ui)

const icons = {
    success: CheckCircleIcon,
    error:   XCircleIcon,
    info:    InformationCircleIcon,
    warning: ExclamationTriangleIcon,
}

const styles = {
    success: 'border-l-4 border-l-brand text-brand',
    error:   'border-l-4 border-l-loss text-loss',
    info:    'border-l-4 border-l-accent-blue text-accent-blue',
    warning: 'border-l-4 border-l-accent-amber text-accent-amber',
}
</script>

<template>
    <div class="fixed bottom-4 right-4 z-[100] flex flex-col gap-2 pointer-events-none">
        <TransitionGroup name="toast">
            <div
                v-for="toast in toasts"
                :key="toast.id"
                :class="['toast pointer-events-auto', styles[toast.variant]]"
            >
                <component :is="icons[toast.variant]" class="h-4 w-4 flex-shrink-0 mt-0.5" />
                <p class="flex-1 text-text-primary text-sm">{{ toast.message }}</p>
                <button
                    class="flex-shrink-0 text-text-tertiary hover:text-text-primary transition-colors"
                    @click="ui.dismissToast(toast.id)"
                >
                    <XMarkIcon class="h-3.5 w-3.5" />
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.toast-enter-active { transition: all 0.3s cubic-bezier(0.34,1.56,0.64,1); }
.toast-leave-active { transition: all 0.2s ease-in; }
.toast-enter-from   { opacity: 0; transform: translateX(20px) scale(0.96); }
.toast-leave-to     { opacity: 0; transform: translateX(20px); }
.toast-move         { transition: transform 0.3s ease; }
</style>
