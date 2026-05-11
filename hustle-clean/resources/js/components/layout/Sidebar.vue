<script setup lang="ts">
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { storeToRefs } from 'pinia'
import { useUiStore } from '@/stores/ui'
import { useAuthStore } from '@/stores/auth'
import {
    Squares2X2Icon,
    BookOpenIcon,
    ChartBarIcon,
    CalendarDaysIcon,
    SparklesIcon,
    Cog6ToothIcon,
    CreditCardIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline'
import type { SharedData } from '@/types'

const ui     = useUiStore()
const auth   = useAuthStore()
const page   = usePage<SharedData>()

const { sidebarOpen, mobileSidebarOpen } = storeToRefs(ui)

const unreadInsights = computed(() => page.props.unread_insights ?? 0)

const navItems = computed(() => [
    { label: 'Dashboard',   href: route('dashboard'),          icon: Squares2X2Icon,     name: 'dashboard' },
    { label: 'Journal',     href: route('journal.index'),      icon: BookOpenIcon,    name: 'journal' },
    { label: 'Analytics',   href: route('analytics.index'),    icon: ChartBarIcon,    name: 'analytics' },
    { label: 'Calendar',    href: route('calendar.index'),     icon: CalendarDaysIcon,name: 'calendar' },
    { label: 'AI Insights', href: route('ai-insights.index'),  icon: SparklesIcon,    name: 'ai-insights', badge: unreadInsights.value },
])

const bottomItems = [
    { label: 'Billing',  href: route('subscription.index'), icon: CreditCardIcon, name: 'subscription' },
    { label: 'Settings', href: route('settings.index'),     icon: Cog6ToothIcon,  name: 'settings' },
]

function isActive(name: string) {
    return page.url.startsWith('/' + name) || route().current(name + '.*') || route().current(name)
}

const avatarInitials = computed(() => {
    const name = auth.user?.name ?? ''
    return name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase()
})
</script>

<template>
    <!-- Mobile -->
    <div
        class="fixed inset-y-0 left-0 z-50 flex lg:hidden"
        :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        style="transition: transform 0.25s cubic-bezier(0.4,0,0.2,1)"
    >
        <div class="relative flex h-full w-64 flex-col glass border-r border-panel-border">
            <button
                class="absolute -right-3 top-6 z-10 flex h-6 w-6 items-center justify-center rounded-full bg-surface border border-panel-border text-text-tertiary hover:text-text-primary"
                @click="ui.closeMobileSidebar"
            >
                <XMarkIcon class="h-3.5 w-3.5" />
            </button>
            <SidebarContent
                :is-open="true"
                :nav-items="navItems"
                :bottom-items="bottomItems"
                :is-active="isActive"
                :avatar-initials="avatarInitials"
            />
        </div>
    </div>

    <!-- Desktop -->
    <div
        class="hidden lg:flex flex-col h-screen border-r border-panel-border bg-surface-400/80 backdrop-blur-2xl flex-shrink-0 transition-all duration-250 ease-smooth"
        :class="sidebarOpen ? 'w-56' : 'w-16'"
    >
        <SidebarContent
            :is-open="sidebarOpen"
            :nav-items="navItems"
            :bottom-items="bottomItems"
            :is-active="isActive"
            :avatar-initials="avatarInitials"
        />

        <!-- Toggle button -->
        <button
            class="absolute bottom-20 -right-3 z-10 hidden lg:flex h-6 w-6 items-center justify-center rounded-full bg-surface-200 border border-panel-border text-text-tertiary hover:text-text-primary hover:border-brand/40 transition-colors duration-150"
            @click="ui.toggleSidebar"
        >
            <ChevronLeftIcon v-if="sidebarOpen" class="h-3.5 w-3.5" />
            <ChevronRightIcon v-else class="h-3.5 w-3.5" />
        </button>
    </div>
</template>

<!-- Inner content component to avoid duplication -->
<script lang="ts">
// Sub-component inline (avoids extra file for small helper)
import { defineComponent, h } from 'vue'
</script>
