<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import { CheckIcon } from '@heroicons/vue/24/solid'
import AppLayout from '@/components/layout/AppLayout.vue'
import type { SubscriptionPlan } from '@/types'

interface Plan {
    id: SubscriptionPlan
    name: string
    price: number
    description: string
    features: string[]
    price_id: string | null
    popular?: boolean
}

const props = defineProps<{
    plans: Plan[]
    subscription: Record<string, unknown> | null
    currentPlan: SubscriptionPlan
}>()

const checkoutForm = useForm({ price_id: '' })

function checkout(priceId: string) {
    checkoutForm.price_id = priceId
    checkoutForm.post(route('subscription.checkout'))
}

function openPortal() {
    window.location.href = route('subscription.portal')
}
</script>

<template>
    <Head title="Abonnement" />
    <AppLayout>
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-10">
                <h1 class="text-3xl font-black tracking-tight mb-2">
                    Kies je <span class="text-gradient-brand">plan</span>
                </h1>
                <p class="text-text-secondary">14 dagen gratis proberen. Geen creditcard vereist.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
                <div
                    v-for="plan in plans"
                    :key="plan.id"
                    :class="['rounded-2xl p-6 relative transition-all duration-200', plan.popular ? 'card-glow ring-1 ring-brand/30' : 'card']"
                >
                    <!-- Popular badge -->
                    <div v-if="plan.popular" class="absolute -top-3 left-1/2 -translate-x-1/2">
                        <span class="tag tag-profit text-xs px-3 py-1">Meest populair</span>
                    </div>

                    <div class="mb-5">
                        <h3 class="text-lg font-bold mb-1">{{ plan.name }}</h3>
                        <p class="text-text-tertiary text-sm mb-3">{{ plan.description }}</p>
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-black">€{{ plan.price }}</span>
                            <span class="text-text-tertiary text-sm">/maand</span>
                        </div>
                    </div>

                    <ul class="space-y-2.5 mb-6">
                        <li v-for="feature in plan.features" :key="feature" class="flex items-start gap-2.5">
                            <CheckIcon class="h-4 w-4 text-brand flex-shrink-0 mt-0.5" />
                            <span class="text-sm text-text-secondary">{{ feature }}</span>
                        </li>
                    </ul>

                    <!-- CTA button -->
                    <template v-if="currentPlan === plan.id">
                        <div class="btn w-full justify-center bg-panel-active text-text-secondary cursor-default">
                            Huidig plan
                        </div>
                        <button
                            v-if="plan.id !== 'free'"
                            class="btn btn-ghost btn-sm w-full mt-2 justify-center"
                            @click="openPortal"
                        >
                            Abonnement beheren
                        </button>
                    </template>
                    <button
                        v-else-if="plan.price_id"
                        :class="['btn w-full justify-center', plan.popular ? 'btn-primary' : 'btn-secondary']"
                        :disabled="checkoutForm.processing"
                        @click="checkout(plan.price_id)"
                    >
                        {{ plan.price === 0 ? 'Gratis starten' : 'Upgraden' }}
                    </button>
                    <Link
                        v-else
                        :href="route('register')"
                        class="btn btn-secondary w-full justify-center"
                    >
                        Gratis starten
                    </Link>
                </div>
            </div>

            <!-- Current subscription details -->
            <div v-if="subscription && currentPlan !== 'free'" class="card p-5">
                <h3 class="text-sm font-semibold mb-3">Abonnement Details</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="stat-label">Status</p>
                        <p class="font-semibold capitalize text-sm">{{ subscription.status }}</p>
                    </div>
                    <div>
                        <p class="stat-label">Plan</p>
                        <p class="font-semibold capitalize text-sm text-brand">{{ currentPlan }}</p>
                    </div>
                    <div v-if="subscription.current_period_end">
                        <p class="stat-label">Verlengt op</p>
                        <p class="font-semibold text-sm">{{ new Date(subscription.current_period_end as string).toLocaleDateString('nl-NL') }}</p>
                    </div>
                    <div>
                        <button class="btn btn-secondary btn-sm" @click="openPortal">
                            Facturatie beheren
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
