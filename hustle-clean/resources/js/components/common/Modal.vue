<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps<{
    show: boolean
    title?: string
    size?: 'sm' | 'md' | 'lg' | 'xl' | 'full'
    closeable?: boolean
}>()

const emit = defineEmits<{
    close: []
}>()

const maxWidths: Record<string, string> = {
    sm:   'max-w-md',
    md:   'max-w-lg',
    lg:   'max-w-2xl',
    xl:   'max-w-4xl',
    full: 'max-w-7xl',
}

function onKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape' && props.closeable !== false) {
        emit('close')
    }
}

onMounted(() => document.addEventListener('keydown', onKeydown))
onUnmounted(() => document.removeEventListener('keydown', onKeydown))
</script>

<template>
    <Teleport to="body">
        <Transition name="modal">
            <div
                v-if="show"
                class="modal-backdrop"
                @mousedown.self="closeable !== false && $emit('close')"
            >
                <div
                    :class="['modal-panel w-full', maxWidths[size ?? 'md']]"
                    role="dialog"
                    aria-modal="true"
                >
                    <!-- Header -->
                    <div v-if="title || $slots.header" class="flex items-center justify-between px-6 py-4 border-b border-panel-border">
                        <slot name="header">
                            <h2 class="text-base font-semibold text-text-primary">{{ title }}</h2>
                        </slot>
                        <button
                            v-if="closeable !== false"
                            class="btn-icon text-text-tertiary hover:text-text-primary"
                            @click="$emit('close')"
                        >
                            <XMarkIcon class="h-4 w-4" />
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-5">
                        <slot />
                    </div>

                    <!-- Footer -->
                    <div v-if="$slots.footer" class="flex items-center justify-end gap-3 px-6 py-4 border-t border-panel-border">
                        <slot name="footer" />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.modal-enter-active { @apply transition duration-200 ease-out; }
.modal-leave-active { @apply transition duration-150 ease-in; }
.modal-enter-from   { @apply opacity-0; }
.modal-leave-to     { @apply opacity-0; }
.modal-enter-from :deep(.modal-panel) { @apply scale-95 translate-y-2; }
.modal-enter-to   :deep(.modal-panel) { @apply scale-100 translate-y-0; }
</style>
