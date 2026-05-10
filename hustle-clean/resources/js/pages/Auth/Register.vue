<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import { EyeIcon, EyeSlashIcon, CheckIcon } from '@heroicons/vue/24/outline'

const showPassword = ref(false)

const form = useForm({
    name:                  '',
    email:                 '',
    password:              '',
    password_confirmation: '',
})

function submit() {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}

const passwordStrength = computed(() => {
    const p = form.password
    if (!p) return 0
    let score = 0
    if (p.length >= 8)  score++
    if (p.length >= 12) score++
    if (/[A-Z]/.test(p)) score++
    if (/[0-9]/.test(p)) score++
    if (/[^A-Za-z0-9]/.test(p)) score++
    return score
})

const strengthLabel = computed(() => ['', 'Zwak', 'Redelijk', 'Goed', 'Sterk', 'Uitstekend'][passwordStrength.value] || '')
const strengthColor = computed(() => ['', 'bg-loss', 'bg-accent-amber', 'bg-accent-amber', 'bg-brand', 'bg-brand'][passwordStrength.value] || '')

import { computed } from 'vue'
</script>

<template>
    <Head title="Registreren" />

    <div class="min-h-screen bg-surface flex">
        <!-- Left panel -->
        <div class="hidden lg:flex flex-col justify-between w-[480px] bg-surface-300 border-r border-panel-border p-10 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand to-brand-600 flex items-center justify-center shadow-glow-brand">
                    <span class="text-base font-black text-surface">H</span>
                </div>
                <span class="text-lg font-bold">Hustle</span>
            </div>

            <div class="space-y-4">
                <h2 class="text-2xl font-bold">Wat je krijgt:</h2>
                <ul class="space-y-3">
                    <li v-for="item in [
                        '14 dagen gratis — geen creditcard nodig',
                        'Volledig Pro plan toegang',
                        'AI coaching van dag 1',
                        'Onbeperkte trades loggen',
                        'Geavanceerde analytics & kalender',
                        'CSV import uit elke broker',
                    ]" :key="item" class="flex items-center gap-2.5">
                        <div class="w-5 h-5 rounded-full bg-brand-muted border border-brand-border flex items-center justify-center flex-shrink-0">
                            <CheckIcon class="h-3 w-3 text-brand" />
                        </div>
                        <span class="text-sm text-text-secondary">{{ item }}</span>
                    </li>
                </ul>
            </div>

            <p class="text-xs text-text-muted">Sluit je aan bij 2,400+ traders die Hustle dagelijks gebruiken.</p>
        </div>

        <!-- Form -->
        <div class="flex-1 flex items-center justify-center p-6">
            <div class="w-full max-w-sm">
                <div class="mb-8">
                    <h1 class="text-2xl font-bold mb-1">Account aanmaken</h1>
                    <p class="text-text-secondary text-sm">Begin je gratis 14-daagse trial</p>
                </div>

                <!-- Google OAuth -->
                <a
                    :href="route('auth.google')"
                    class="flex items-center justify-center gap-3 w-full py-2.5 px-4 rounded-xl border border-panel-border bg-panel hover:bg-panel-hover transition-colors duration-150 text-sm font-medium mb-5"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Doorgaan met Google
                </a>

                <div class="flex items-center gap-3 mb-5">
                    <div class="flex-1 divider" />
                    <span class="text-xs text-text-muted">of</span>
                    <div class="flex-1 divider" />
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="label">Volledige naam</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="input"
                            placeholder="Jan de Vries"
                            autocomplete="name"
                            required
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-loss">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="label">E-mailadres</label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="input"
                            placeholder="jan@hustle.app"
                            autocomplete="email"
                            required
                        />
                        <p v-if="form.errors.email" class="mt-1 text-xs text-loss">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label class="label">Wachtwoord</label>
                        <div class="relative">
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                class="input pr-10"
                                placeholder="Minimaal 8 tekens"
                                autocomplete="new-password"
                                required
                            />
                            <button
                                type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-text-muted hover:text-text-secondary"
                                @click="showPassword = !showPassword"
                            >
                                <EyeSlashIcon v-if="showPassword" class="h-4 w-4" />
                                <EyeIcon v-else class="h-4 w-4" />
                            </button>
                        </div>

                        <!-- Password strength -->
                        <div v-if="form.password" class="mt-2 space-y-1">
                            <div class="flex gap-1">
                                <div
                                    v-for="i in 5"
                                    :key="i"
                                    :class="['h-1 flex-1 rounded-full transition-all duration-300', i <= passwordStrength ? strengthColor : 'bg-panel-active']"
                                />
                            </div>
                            <p class="text-xs text-text-muted">{{ strengthLabel }}</p>
                        </div>

                        <p v-if="form.errors.password" class="mt-1 text-xs text-loss">{{ form.errors.password }}</p>
                    </div>

                    <div>
                        <label class="label">Wachtwoord bevestigen</label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            class="input"
                            placeholder="Herhaal wachtwoord"
                            autocomplete="new-password"
                            required
                        />
                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary w-full py-2.5"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing" class="animate-spin w-4 h-4 border-2 border-surface border-t-transparent rounded-full" />
                        {{ form.processing ? 'Account aanmaken...' : 'Gratis beginnen' }}
                    </button>
                </form>

                <p class="text-center text-xs text-text-muted mt-5 leading-relaxed">
                    Door te registreren ga je akkoord met onze
                    <a href="#" class="text-brand hover:underline">gebruiksvoorwaarden</a>
                    en
                    <a href="#" class="text-brand hover:underline">privacybeleid</a>.
                </p>

                <p class="text-center text-sm text-text-tertiary mt-4">
                    Al een account?
                    <Link :href="route('login')" class="text-brand hover:underline font-medium">Inloggen</Link>
                </p>
            </div>
        </div>
    </div>
</template>
