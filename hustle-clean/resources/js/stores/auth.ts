import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import type { User, Account, SharedData } from '@/types'

export const useAuthStore = defineStore('auth', () => {
    const page = usePage<SharedData>()

    const user = computed(() => page.props.auth.user as User | null)
    const isAuthenticated = computed(() => !!user.value)

    const isPro = computed(() =>
        user.value?.subscription_plan === 'pro' || user.value?.subscription_plan === 'premium'
    )
    const isPremium = computed(() => user.value?.subscription_plan === 'premium')

    function logout() {
        router.post(route('logout'))
    }

    return {
        user,
        isAuthenticated,
        isPro,
        isPremium,
        logout,
    }
})
