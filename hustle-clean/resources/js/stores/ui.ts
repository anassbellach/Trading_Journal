import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export type ToastVariant = 'success' | 'error' | 'info' | 'warning'

interface Toast {
    id: string
    message: string
    variant: ToastVariant
    duration: number
}

export const useUiStore = defineStore('ui', () => {
    // Sidebar
    const sidebarOpen = ref(true)
    const mobileSidebarOpen = ref(false)

    function toggleSidebar() {
        sidebarOpen.value = !sidebarOpen.value
    }

    function openMobileSidebar() {
        mobileSidebarOpen.value = true
    }

    function closeMobileSidebar() {
        mobileSidebarOpen.value = false
    }

    // Toasts
    const toasts = ref<Toast[]>([])

    function toast(message: string, variant: ToastVariant = 'info', duration = 4000) {
        const id = Math.random().toString(36).slice(2)
        toasts.value.push({ id, message, variant, duration })
        setTimeout(() => dismissToast(id), duration)
    }

    function dismissToast(id: string) {
        const idx = toasts.value.findIndex(t => t.id === id)
        if (idx !== -1) toasts.value.splice(idx, 1)
    }

    // Active modal
    const activeModal = ref<string | null>(null)
    const modalData   = ref<Record<string, unknown>>({})

    function openModal(name: string, data: Record<string, unknown> = {}) {
        activeModal.value = name
        modalData.value   = data
    }

    function closeModal() {
        activeModal.value = null
        modalData.value   = {}
    }

    return {
        sidebarOpen,
        mobileSidebarOpen,
        toggleSidebar,
        openMobileSidebar,
        closeMobileSidebar,
        toasts,
        toast,
        dismissToast,
        activeModal,
        modalData,
        openModal,
        closeModal,
    }
})
