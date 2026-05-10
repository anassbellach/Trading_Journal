<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { storeToRefs } from 'pinia'
import { useUiStore } from '@/stores/ui'
import Sidebar from '@/components/layout/Sidebar.vue'
import Topbar from '@/components/layout/Topbar.vue'
import ToastContainer from '@/components/common/ToastContainer.vue'
import type { SharedData } from '@/types'

const ui = useUiStore()
const { sidebarOpen, mobileSidebarOpen } = storeToRefs(ui)
const page = usePage<SharedData>()

// Handle flash messages from server
const flash = computed(() => page.props.flash)
</script>

<template>
    <div class="flex h-screen bg-surface overflow-hidden">
        <!-- Mobile sidebar overlay -->
        <Transition name="fade">
            <div
                v-if="mobileSidebarOpen"
                class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"
                @click="ui.closeMobileSidebar"
            />
        </Transition>

        <!-- Sidebar -->
        <Sidebar />

        <!-- Main content -->
        <div class="flex flex-col flex-1 min-w-0 overflow-hidden">
            <Topbar />

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto">
                <div class="p-6 max-w-[1600px] mx-auto w-full">
                    <slot />
                </div>
            </main>
        </div>

        <!-- Toast container -->
        <ToastContainer />
    </div>
</template>
