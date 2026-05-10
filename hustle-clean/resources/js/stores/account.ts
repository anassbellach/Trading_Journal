import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import type { Account, SharedData } from '@/types'

export const useAccountStore = defineStore('account', () => {
    const page = usePage<SharedData>()

    const accounts = computed(() => page.props.accounts as Account[])
    const activeAccount = computed(() => page.props.activeAccount as Account | null)

    function switchAccount(accountId: number) {
        router.post(route('accounts.switch'), { account_id: accountId }, {
            preserveState: false,
        })
    }

    return {
        accounts,
        activeAccount,
        switchAccount,
    }
})
