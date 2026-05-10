<script setup lang="ts">
// Login.vue
import { Head, useForm, Link } from '@inertiajs/vue3'
import { EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline'
import { ref } from 'vue'

const showPassword = ref(false)

const form = useForm({
    email:    '',
    password: '',
    remember: false,
})

function submit() {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <Head title="Inloggen" />

    <div class="min-h-screen bg-surface flex">
        <!-- Left panel: branding -->
        <div class="hidden lg:flex flex-col justify-between w-[480px] bg-surface-300 border-r border-panel-border p-10 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand to-brand-600 flex items-center justify-center shadow-glow-brand">
                    <span class="text-base font-black text-surface">H</span>
                </div>
                <span class="text-lg font-bold tracking-tight">Hustle</span>
            </div>

            <div>
                <blockquote class="text-xl font-medium text-text-primary leading-relaxed mb-4">
                    "Het verschil tussen winnen en verliezen traders is niet de strategie — het is consistentie in journaling."
                </blockquote>
                <p class="text-sm text-text-tertiary">— Mark Douglas, Trading in the Zone</p>
            </div>

            <div class="flex items-center gap-2">
                <div class="flex -space-x-2">
                    <div v-for="i in 4" :key="i" class="w-7 h-7 rounded-full border-2 border-surface-300 bg-gradient-to-br from-brand to-accent-purple" />
                </div>
                <p class="text-xs text-text-tertiary">Sluit aan bij 2,400+ traders</p>
            </div>
        </div>

        <!-- Right panel: form -->
        <div class="flex-1 flex items-center justify-center p-6">
            <div class="w-full max-w-sm">
                <div class="mb-8">
                    <h1 class="text-2xl font-bold mb-1">Welkom terug</h1>
                    <p class="text-text-secondary text-sm">Log in op je Hustle account</p>
                </div>

                <!-- Google OAuth -->
                <a
                    :href="route('auth.google')"
                    class="flex items-center justify-center gap-3 w-full py-2.5 px-4 rounded-xl border border-panel-border bg-panel hover:bg-panel-hover transition-colors duration-150 text-sm font-medium mb-5"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                    Doorgaan met Google
                </a>

                <div class="flex items-center gap-3 mb-5">
                    <div class="flex-1 divider" />
                    <span class="text-xs text-text-muted">of</span>
                    <div class="flex-1 divider" />
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="label">E-mailadres</label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="input"
                            placeholder="jij@hustle.app"
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
                                placeholder="••••••••"
                                autocomplete="current-password"
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
                        <p v-if="form.errors.password" class="mt-1 text-xs text-loss">{{ form.errors.password }}</p>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input v-model="form.remember" type="checkbox" class="form-checkbox rounded border-panel-border bg-panel text-brand focus:ring-brand/30" />
                            <span class="text-sm text-text-secondary">Onthoud mij</span>
                        </label>
                        <Link href="#" class="text-xs text-brand hover:underline">Wachtwoord vergeten?</Link>
                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary w-full py-2.5"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing" class="animate-spin w-4 h-4 border-2 border-surface border-t-transparent rounded-full" />
                        {{ form.processing ? 'Inloggen...' : 'Inloggen' }}
                    </button>
                </form>

                <p class="text-center text-sm text-text-tertiary mt-6">
                    Nog geen account?
                    <Link :href="route('register')" class="text-brand hover:underline font-medium">Gratis starten</Link>
                </p>
            </div>
        </div>
    </div>
</template>
