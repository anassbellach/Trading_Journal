<script setup lang="ts">
import { computed, ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { storeToRefs } from 'pinia'
import { useUiStore } from '@/stores/ui'
import { useAuthStore } from '@/stores/auth'
import { useAccountStore } from '@/stores/account'
import {
    Bars3Icon,
    PlusIcon,
    BellIcon,
    ChevronDownIcon,
    CheckIcon,
} from '@heroicons/vue/24/outline'
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import type { SharedData } from '@/types'

const ui      = useUiStore()
const auth    = useAuthStore()
const account = useAccountStore()
const page    = usePage<SharedData>()

const { sidebarOpen } = storeToRefs(ui)
const { accounts, activeAccount } = storeToRefs(account)

const pageTitle = computed(() => {
    const url = page.url
    if (url.startsWith('/dashboard')) return 'Dashboard'
    if (url.startsWith('/journal'))   return 'Trade Journal'
    if (url.startsWith('/analytics')) return 'Analytics'
    if (url.startsWith('/calendar'))  return 'Calendar'
    if (url.startsWith('/ai-insights')) return 'AI Insights'
    if (url.startsWith('/settings'))  return 'Settings'
    if (url.startsWith('/subscription')) return 'Billing'
    return 'Hustle'
})

const unreadInsights = computed(() => page.props.unread_insights ?? 0)

function openAddTrade() {
    ui.openModal('add-trade')
}
</script>

<template>
    <header class="flex-shrink-0 h-14 flex items-center justify-between px-5 border-b border-panel-border bg-surface-400/80 backdrop-blur-xl">
        <!-- Left: menu toggle + page title -->
        <div class="flex items-center gap-3">
            <button
                class="btn-icon text-text-tertiary hover:text-text-primary hidden lg:flex"
                @click="ui.toggleSidebar"
            >
                <Bars3Icon class="h-4 w-4" />
            </button>
            <button
                class="btn-icon text-text-tertiary hover:text-text-primary lg:hidden"
                @click="ui.openMobileSidebar"
            >
                <Bars3Icon class="h-4 w-4" />
            </button>

            <h1 class="text-base font-semibold text-text-primary tracking-tight">
                {{ pageTitle }}
            </h1>
        </div>

        <!-- Right: account switcher + actions -->
        <div class="flex items-center gap-2">
            <!-- Account switcher -->
            <Menu as="div" class="relative">
                <MenuButton class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-panel hover:bg-panel-hover border border-panel-border text-sm font-medium text-text-primary transition-all duration-150">
                    <span class="w-2 h-2 rounded-full bg-brand flex-shrink-0" />
                    <span class="max-w-[140px] truncate">{{ activeAccount?.name ?? 'No Account' }}</span>
                    <ChevronDownIcon class="h-3.5 w-3.5 text-text-tertiary flex-shrink-0" />
                </MenuButton>

                <Transition
                    enter-active-class="transition duration-100 ease-out"
                    enter-from-class="transform scale-95 opacity-0"
                    enter-to-class="transform scale-100 opacity-1"
                    leave-active-class="transition duration-75 ease-in"
                    leave-from-class="transform scale-100 opacity-100"
                    leave-to-class="transform scale-95 opacity-0"
                >
                    <MenuItems class="absolute right-0 mt-2 w-56 rounded-xl shadow-dropdown bg-surface-200 border border-panel-border z-50 py-1 focus:outline-none">
                        <div class="px-3 py-2 text-xs font-semibold text-text-tertiary uppercase tracking-wider">
                            Accounts
                        </div>
                        <MenuItem
                            v-for="acc in accounts"
                            :key="acc.id"
                            v-slot="{ active }"
                        >
                            <button
                                :class="['flex items-center justify-between w-full px-3 py-2 text-sm transition-colors duration-100', active ? 'bg-panel-hover text-text-primary' : 'text-text-secondary']"
                                @click="account.switchAccount(acc.id)"
                            >
                                <div class="flex items-center gap-2.5">
                                    <div class="w-6 h-6 rounded-md bg-panel-active flex items-center justify-center">
                                        <span class="text-xs font-bold text-text-secondary">{{ acc.name[0] }}</span>
                                    </div>
                                    <div class="text-left">
                                        <div class="font-medium text-xs leading-tight">{{ acc.name }}</div>
                                        <div class="text-text-tertiary text-xs">{{ acc.broker }}</div>
                                    </div>
                                </div>
                                <CheckIcon v-if="acc.id === activeAccount?.id" class="h-3.5 w-3.5 text-brand" />
                            </button>
                        </MenuItem>
                        <div class="border-t border-panel-border mt-1 pt-1">
                            <Link
                                :href="route('accounts.create')"
                                class="flex items-center gap-2 px-3 py-2 text-xs text-text-tertiary hover:text-brand transition-colors duration-100"
                            >
                                <PlusIcon class="h-3.5 w-3.5" />
                                Add Account
                            </Link>
                        </div>
                    </MenuItems>
                </Transition>
            </Menu>

            <!-- Log trade button -->
            <button class="btn btn-primary btn-sm hidden sm:flex" @click="openAddTrade">
                <PlusIcon class="h-3.5 w-3.5" />
                Log Trade
            </button>

            <!-- Notifications -->
            <Link :href="route('ai-insights.index')" class="relative btn-icon text-text-tertiary hover:text-text-primary">
                <BellIcon class="h-4.5 w-4.5" />
                <span
                    v-if="unreadInsights > 0"
                    class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 px-1 flex items-center justify-center bg-brand text-surface text-[9px] font-bold rounded-full"
                >
                    {{ unreadInsights > 9 ? '9+' : unreadInsights }}
                </span>
            </Link>
        </div>
    </header>
</template>
