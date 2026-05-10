import { watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useUiStore } from '@/stores/ui'
import type { SharedData } from '@/types'

/**
 * Automatically show toast notifications for Inertia flash messages.
 * Use this composable in your root layout component.
 */
export function useFlash() {
    const page = usePage<SharedData>()
    const ui   = useUiStore()

    watch(
        () => page.props.flash,
        (flash) => {
            if (flash.success) ui.toast(flash.success, 'success')
            if (flash.error)   ui.toast(flash.error,   'error')
            if (flash.info)    ui.toast(flash.info,     'info')
        },
        { deep: true }
    )
}
