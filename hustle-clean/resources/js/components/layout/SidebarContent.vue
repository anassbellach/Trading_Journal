<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { useAuthStore } from '@/stores/auth'
import type { Component } from 'vue'

interface NavItem {
    label: string
    href: string
    icon: Component
    name: string
    badge?: number
}

defineProps<{
    isOpen: boolean
    navItems: NavItem[]
    bottomItems: NavItem[]
    isActive: (name: string) => boolean
    avatarInitials: string
}>()

const auth = useAuthStore()
</script>

<template>
    <div class="flex flex-col h-full overflow-hidden">
        <!-- Logo -->
        <div class="flex items-center gap-3 px-4 py-5 border-b border-panel-border flex-shrink-0">
            <div class="flex-shrink-0 w-8 h-8 rounded-xl bg-gradient-to-br from-brand to-brand-600 flex items-center justify-center shadow-glow-sm">
                <span class="text-sm font-black text-surface">H</span>
            </div>
            <Transition name="fade">
                <span v-if="isOpen" class="text-base font-bold tracking-tight text-text-primary">
                    Hustle
                </span>
            </Transition>
        </div>

        <!-- Primary nav -->
        <nav class="flex-1 px-2 py-3 space-y-0.5 overflow-y-auto hide-scrollbar">
            <Link
                v-for="item in navItems"
                :key="item.name"
                :href="item.href"
                :class="[
                    isActive(item.name) ? 'nav-item-active' : 'nav-item',
                    !isOpen && 'justify-center px-2',
                ]"
                :title="!isOpen ? item.label : undefined"
            >
                <component
                    :is="item.icon"
                    :class="['flex-shrink-0', isActive(item.name) ? 'h-[18px] w-[18px] text-brand' : 'h-[18px] w-[18px]']"
                />
                <Transition name="fade">
                    <span v-if="isOpen" class="flex-1 truncate text-sm">{{ item.label }}</span>
                </Transition>
                <Transition name="fade">
                    <span
                        v-if="isOpen && item.badge && item.badge > 0"
                        class="flex-shrink-0 px-1.5 py-0.5 text-xs font-bold rounded-full bg-brand-muted text-brand border border-brand-border"
                    >
                        {{ item.badge }}
                    </span>
                </Transition>
            </Link>
        </nav>

        <!-- Bottom nav -->
        <div class="px-2 py-3 space-y-0.5 border-t border-panel-border flex-shrink-0">
            <Link
                v-for="item in bottomItems"
                :key="item.name"
                :href="item.href"
                :class="[
                    isActive(item.name) ? 'nav-item-active' : 'nav-item',
                    !isOpen && 'justify-center px-2',
                ]"
                :title="!isOpen ? item.label : undefined"
            >
                <component
                    :is="item.icon"
                    :class="['flex-shrink-0 h-[18px] w-[18px]', isActive(item.name) ? 'text-brand' : '']"
                />
                <Transition name="fade">
                    <span v-if="isOpen" class="text-sm truncate">{{ item.label }}</span>
                </Transition>
            </Link>
        </div>

        <!-- User card -->
        <div class="px-2 pb-4 flex-shrink-0">
            <div
                :class="[
                    'flex items-center gap-2.5 p-2 rounded-xl bg-panel hover:bg-panel-hover cursor-pointer transition-colors duration-150',
                    !isOpen && 'justify-center',
                ]"
            >
                <div class="flex-shrink-0 w-7 h-7 rounded-full bg-gradient-to-br from-brand to-accent-purple flex items-center justify-center text-xs font-bold text-white">
                    {{ avatarInitials }}
                </div>
                <Transition name="fade">
                    <div v-if="isOpen" class="flex-1 min-w-0">
                        <div class="text-xs font-semibold text-text-primary truncate">{{ auth.user?.name }}</div>
                        <div class="text-xs text-text-tertiary capitalize">{{ auth.user?.subscription_plan }} Plan</div>
                    </div>
                </Transition>
            </div>
        </div>
    </div>
</template>
