<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/components/layout/AppLayout.vue'
import { useUiStore } from '@/stores/ui'
import type { User } from '@/types'

const props = defineProps<{ user: User }>()
const ui = useUiStore()

const profileForm = useForm({
    name:     props.user.name,
    email:    props.user.email,
    timezone: (props.user as any).timezone ?? 'Europe/Amsterdam',
})

const passwordForm = useForm({
    current_password:      '',
    password:              '',
    password_confirmation: '',
})

const timezones = [
    'Europe/Amsterdam', 'Europe/London', 'Europe/Paris', 'Europe/Berlin',
    'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles',
    'Asia/Tokyo', 'Asia/Singapore', 'Asia/Dubai', 'Australia/Sydney',
]

function saveProfile() {
    profileForm.put(route('settings.profile'), {
        onSuccess: () => ui.toast('Profiel opgeslagen', 'success'),
    })
}

function savePassword() {
    passwordForm.put(route('settings.password'), {
        onSuccess: () => {
            ui.toast('Wachtwoord gewijzigd', 'success')
            passwordForm.reset()
        },
    })
}
</script>

<template>
    <Head title="Instellingen" />
    <AppLayout>
        <div class="max-w-2xl space-y-5">

            <!-- Profile -->
            <div class="card p-6">
                <h2 class="text-sm font-semibold mb-5">Profiel</h2>
                <form @submit.prevent="saveProfile" class="space-y-4">
                    <div>
                        <label class="label">Naam</label>
                        <input v-model="profileForm.name" class="input" placeholder="Je naam" />
                        <p v-if="profileForm.errors.name" class="mt-1 text-xs text-loss">{{ profileForm.errors.name }}</p>
                    </div>
                    <div>
                        <label class="label">E-mailadres</label>
                        <input v-model="profileForm.email" type="email" class="input" />
                        <p v-if="profileForm.errors.email" class="mt-1 text-xs text-loss">{{ profileForm.errors.email }}</p>
                    </div>
                    <div>
                        <label class="label">Tijdzone</label>
                        <select v-model="profileForm.timezone" class="input-select">
                            <option v-for="tz in timezones" :key="tz" :value="tz">{{ tz }}</option>
                        </select>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="btn btn-primary btn-sm" :disabled="profileForm.processing">
                            Opslaan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Password -->
            <div class="card p-6">
                <h2 class="text-sm font-semibold mb-5">Wachtwoord Wijzigen</h2>
                <form @submit.prevent="savePassword" class="space-y-4">
                    <div>
                        <label class="label">Huidig wachtwoord</label>
                        <input v-model="passwordForm.current_password" type="password" class="input" />
                        <p v-if="passwordForm.errors.current_password" class="mt-1 text-xs text-loss">{{ passwordForm.errors.current_password }}</p>
                    </div>
                    <div>
                        <label class="label">Nieuw wachtwoord</label>
                        <input v-model="passwordForm.password" type="password" class="input" />
                    </div>
                    <div>
                        <label class="label">Bevestig wachtwoord</label>
                        <input v-model="passwordForm.password_confirmation" type="password" class="input" />
                        <p v-if="passwordForm.errors.password" class="mt-1 text-xs text-loss">{{ passwordForm.errors.password }}</p>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="btn btn-primary btn-sm" :disabled="passwordForm.processing">
                            Wachtwoord Wijzigen
                        </button>
                    </div>
                </form>
            </div>

            <!-- Danger zone -->
            <div class="card p-6 border border-loss/20">
                <h2 class="text-sm font-semibold mb-3 text-loss-text">Gevarenzone</h2>
                <p class="text-xs text-text-tertiary mb-4">Deze acties zijn onomkeerbaar. Ga voorzichtig te werk.</p>
                <div class="flex gap-3">
                    <button class="btn btn-danger btn-sm">Account verwijderen</button>
                    <button class="btn btn-secondary btn-sm">Alle data exporteren</button>
                </div>
            </div>

            <!-- Subscription shortcut -->
            <div class="card p-5 flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold">Abonnement</p>
                    <p class="text-xs text-text-tertiary mt-0.5 capitalize">{{ user.subscription_plan }} plan</p>
                </div>
                <Link :href="route('subscription.index')" class="btn btn-secondary btn-sm">Beheren →</Link>
            </div>
        </div>
    </AppLayout>
</template>
