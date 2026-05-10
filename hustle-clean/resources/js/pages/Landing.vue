<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import {
    ChartBarIcon,
    SparklesIcon,
    CalendarDaysIcon,
    ShieldCheckIcon,
    ArrowRightIcon,
    CheckIcon,
} from '@heroicons/vue/24/outline'

const annualBilling = ref(false)

const features = [
    {
        icon: ChartBarIcon,
        title: 'Geavanceerde Analytics',
        desc: 'Diepgaande statistieken: win rate per strategie, sessie, weekdag, RR-verdeling en equity curve.',
    },
    {
        icon: SparklesIcon,
        title: 'AI Trading Coach',
        desc: 'Automatische detectie van revenge trading, overhandelen en je sterkste edge. Gepersonaliseerde wekelijkse samenvatting.',
    },
    {
        icon: CalendarDaysIcon,
        title: 'PnL Kalender Heatmap',
        desc: 'Visualiseer je prestaties per dag, week en maand. Zie direct welke dagen en sessies het beste werken.',
    },
    {
        icon: ShieldCheckIcon,
        title: 'Prop-Firma Ready',
        desc: 'Dagelijkse verlieslimieten, drawdown tracking en risicoalerts speciaal voor funded accounts.',
    },
]

const plans = [
    {
        id: 'free',
        name: 'Free',
        monthlyPrice: 0,
        desc: 'Begin gratis',
        features: ['Tot 50 trades/maand', '1 account', 'Basis journal', 'Eenvoudige analytics'],
        cta: 'Gratis starten',
        href: '/register',
        highlight: false,
    },
    {
        id: 'pro',
        name: 'Pro',
        monthlyPrice: 29,
        annualPrice: 23,
        desc: 'Voor serieuze traders',
        features: [
            'Onbeperkte trades',
            '5 accounts',
            'Geavanceerde analytics',
            'AI inzichten',
            'CSV import/export',
            '14 dagen gratis',
        ],
        cta: 'Start Pro Trial',
        href: '/register?plan=pro',
        highlight: true,
    },
    {
        id: 'premium',
        name: 'Premium',
        monthlyPrice: 59,
        annualPrice: 47,
        desc: 'Voor prop traders',
        features: [
            'Alles in Pro',
            'Onbeperkte accounts',
            'Prioritaire AI analyse',
            'Doelen & streak tracking',
            'Vroege toegang',
            'Prioritaire support',
        ],
        cta: 'Start Premium Trial',
        href: '/register?plan=premium',
        highlight: false,
    },
]

const testimonials = [
    {
        text: 'Hustle heeft mijn trading compleet veranderd. De AI detecteerde mijn revenge trading patroon en sindsdien is mijn win rate met 15% gestegen.',
        name: 'Thomas V.',
        role: 'NQ Futures Trader · Apex Funded',
        avatar: 'TV',
    },
    {
        text: 'Eindelijk een journal dat echt begrijpt wat traders nodig hebben. De analytics zijn op het niveau van professionele tools maar betaalbaar.',
        name: 'Rania K.',
        role: 'Forex & Indices Trader',
        avatar: 'RK',
    },
    {
        text: 'De sessie-analyse liet zien dat 80% van mijn verliezen in de Asian sessie waren. Ik handel nu alleen nog NY en mijn resultaten zijn enorm verbeterd.',
        name: 'Lars D.',
        role: 'ES & MES Futures Trader',
        avatar: 'LD',
    },
]

const stats = [
    { value: '2,400+', label: 'Actieve traders' },
    { value: '$12M+', label: 'Gelogde PnL' },
    { value: '180K+', label: 'Trades geanalyseerd' },
    { value: '4.9★', label: 'Gemiddelde score' },
]
</script>

<template>
    <Head title="Hustle — Premium Trading Journal" />

    <div class="min-h-screen bg-surface text-text-primary font-sans">

        <!-- ─── Nav ─────────────────────────────────────────────────────────── -->
        <nav class="fixed top-0 left-0 right-0 z-50 h-14 flex items-center justify-between px-6 lg:px-12 border-b border-panel-border bg-surface/80 backdrop-blur-xl">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-brand to-brand-600 flex items-center justify-center shadow-glow-sm">
                    <span class="text-sm font-black text-surface">H</span>
                </div>
                <span class="text-base font-bold tracking-tight">Hustle</span>
            </div>

            <div class="hidden md:flex items-center gap-6 text-sm text-text-secondary">
                <a href="#features" class="hover:text-text-primary transition-colors">Features</a>
                <a href="#pricing"  class="hover:text-text-primary transition-colors">Prijzen</a>
                <a href="#testimonials" class="hover:text-text-primary transition-colors">Reviews</a>
            </div>

            <div class="flex items-center gap-3">
                <Link :href="route('login')" class="text-sm text-text-secondary hover:text-text-primary transition-colors hidden sm:block">
                    Inloggen
                </Link>
                <Link :href="route('register')" class="btn btn-primary btn-sm">
                    Gratis starten
                </Link>
            </div>
        </nav>

        <!-- ─── Hero ────────────────────────────────────────────────────────── -->
        <section class="pt-32 pb-24 px-6 text-center">
            <div class="max-w-4xl mx-auto">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-brand-muted border border-brand-border text-brand text-xs font-semibold mb-6">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand animate-pulse-brand" />
                    Nu beschikbaar — 14 dagen gratis proberen
                </div>

                <h1 class="text-5xl lg:text-7xl font-black tracking-tight leading-tight mb-6">
                    Het journal dat je<br />
                    <span class="text-gradient-brand">trading verbetert</span>
                </h1>

                <p class="text-lg lg:text-xl text-text-secondary max-w-2xl mx-auto mb-10 leading-relaxed">
                    Premium analytics, AI coaching en visuele tracking voor serieuze futures- en forextraders.
                    Stop met gokken waarom je verliest — begin met weten.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mb-16">
                    <Link :href="route('register')" class="btn btn-primary px-8 py-3 text-base shadow-glow-brand">
                        Gratis beginnen
                        <ArrowRightIcon class="h-4 w-4" />
                    </Link>
                    <Link :href="route('login')" class="btn btn-secondary px-8 py-3 text-base">
                        Inloggen
                    </Link>
                </div>

                <!-- Stats bar -->
                <div class="flex flex-wrap items-center justify-center gap-8 lg:gap-16">
                    <div v-for="stat in stats" :key="stat.label" class="text-center">
                        <div class="text-2xl font-black text-text-primary">{{ stat.value }}</div>
                        <div class="text-xs text-text-muted mt-0.5">{{ stat.label }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ─── App preview ─────────────────────────────────────────────────── -->
        <section class="px-6 pb-24">
            <div class="max-w-6xl mx-auto">
                <div class="rounded-3xl border border-panel-border bg-surface-300 overflow-hidden shadow-modal">
                    <!-- Fake browser chrome -->
                    <div class="flex items-center gap-2 px-4 py-3 border-b border-panel-border bg-surface-400/60">
                        <div class="w-2.5 h-2.5 rounded-full bg-loss/60" />
                        <div class="w-2.5 h-2.5 rounded-full bg-accent-amber/60" />
                        <div class="w-2.5 h-2.5 rounded-full bg-profit/60" />
                        <div class="flex-1 ml-3 bg-panel rounded-md px-3 py-1 text-xs text-text-muted text-center max-w-sm mx-auto">
                            app.hustle.trading/dashboard
                        </div>
                    </div>

                    <!-- Mock dashboard screenshot placeholder -->
                    <div class="p-6 bg-surface">
                        <!-- Stats row mock -->
                        <div class="grid grid-cols-4 gap-3 mb-4">
                            <div v-for="(s, i) in [
                                { label: 'Totale PnL', value: '+$7,900', color: 'text-profit' },
                                { label: 'Win Rate', value: '62.5%', color: 'text-accent-blue' },
                                { label: 'Profit Factor', value: '2.41', color: 'text-accent-amber' },
                                { label: 'Gem. RR', value: '+2.44R', color: 'text-accent-rose' },
                            ]" :key="i" class="card p-4">
                                <p class="stat-label">{{ s.label }}</p>
                                <p :class="['text-xl font-bold num', s.color]">{{ s.value }}</p>
                            </div>
                        </div>

                        <!-- Equity curve mock -->
                        <div class="card p-4 mb-3">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-semibold">Equity Curve</span>
                                <div class="flex gap-1">
                                    <span v-for="p in ['1W','1M','3M','YTD']" :key="p" :class="['tag text-xs', p === '1M' ? 'tag-profit' : 'tag-neutral']">{{ p }}</span>
                                </div>
                            </div>
                            <!-- SVG curve placeholder -->
                            <div class="h-28 rounded-xl bg-gradient-to-b from-brand/10 to-transparent flex items-end px-3 pb-3 gap-1">
                                <div v-for="(h, i) in [30, 45, 38, 60, 52, 75, 68, 85, 78, 95, 88, 100]" :key="i"
                                    :style="{ height: `${h}%` }"
                                    class="flex-1 bg-gradient-to-t from-brand/80 to-brand/20 rounded-sm"
                                />
                            </div>
                        </div>

                        <!-- Trade table mock -->
                        <div class="card overflow-hidden">
                            <div class="grid grid-cols-6 gap-4 px-4 py-2 border-b border-panel-border text-xs text-text-muted uppercase tracking-wider">
                                <span>Ticker</span><span>Richting</span><span>Entry</span><span>PnL</span><span>RR</span><span>Sessie</span>
                            </div>
                            <div v-for="(row, i) in [
                                { ticker: 'NQ', dir: 'long',  entry: '21340', pnl: '+$900',   rr: '+3.0R', session: 'NY', win: true  },
                                { ticker: 'ES', dir: 'short', entry: '5890',  pnl: '+$500',   rr: '+2.5R', session: 'EU', win: true  },
                                { ticker: 'GC', dir: 'long',  entry: '3310',  pnl: '-$300',   rr: '-1.0R', session: 'NY', win: false },
                                { ticker: 'NQ', dir: 'long',  entry: '21100', pnl: '+$1,100', rr: '+3.6R', session: 'NY', win: true  },
                            ]" :key="i"
                                class="grid grid-cols-6 gap-4 px-4 py-3 border-b border-panel-border/50 text-sm hover:bg-panel-hover transition-colors"
                            >
                                <span class="font-bold">{{ row.ticker }}</span>
                                <span :class="['tag text-xs w-fit', row.dir === 'long' ? 'tag-profit' : 'tag-loss']">{{ row.dir }}</span>
                                <span class="num text-text-secondary">{{ row.entry }}</span>
                                <span :class="['font-bold num', row.win ? 'text-profit' : 'text-loss']">{{ row.pnl }}</span>
                                <span :class="['num font-semibold', row.win ? 'text-profit-text' : 'text-loss-text']">{{ row.rr }}</span>
                                <span class="tag tag-blue text-xs w-fit">{{ row.session }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ─── Features ────────────────────────────────────────────────────── -->
        <section id="features" class="py-24 px-6 border-t border-panel-border">
            <div class="max-w-5xl mx-auto">
                <div class="text-center mb-14">
                    <h2 class="text-3xl lg:text-4xl font-black tracking-tight mb-3">
                        Alles wat je nodig hebt
                    </h2>
                    <p class="text-text-secondary text-lg max-w-xl mx-auto">
                        Van trade logging tot AI coaching — Hustle dekt de volledige cyclus van analyse en verbetering.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div
                        v-for="feature in features"
                        :key="feature.title"
                        class="card-hover p-6"
                    >
                        <div class="w-10 h-10 rounded-xl bg-brand-muted border border-brand-border flex items-center justify-center mb-4">
                            <component :is="feature.icon" class="h-5 w-5 text-brand" />
                        </div>
                        <h3 class="text-base font-bold mb-2">{{ feature.title }}</h3>
                        <p class="text-sm text-text-secondary leading-relaxed">{{ feature.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ─── Testimonials ────────────────────────────────────────────────── -->
        <section id="testimonials" class="py-24 px-6 border-t border-panel-border">
            <div class="max-w-5xl mx-auto">
                <div class="text-center mb-14">
                    <h2 class="text-3xl font-black tracking-tight mb-3">Traders over Hustle</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div v-for="t in testimonials" :key="t.name" class="card p-6">
                        <div class="flex gap-1 mb-4">
                            <span v-for="i in 5" :key="i" class="text-accent-amber text-sm">★</span>
                        </div>
                        <p class="text-sm text-text-secondary leading-relaxed mb-4">"{{ t.text }}"</p>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand to-accent-purple flex items-center justify-center text-xs font-bold text-white flex-shrink-0">
                                {{ t.avatar }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold">{{ t.name }}</p>
                                <p class="text-xs text-text-muted">{{ t.role }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ─── Pricing ─────────────────────────────────────────────────────── -->
        <section id="pricing" class="py-24 px-6 border-t border-panel-border">
            <div class="max-w-5xl mx-auto">
                <div class="text-center mb-10">
                    <h2 class="text-3xl lg:text-4xl font-black tracking-tight mb-3">
                        Transparante prijzen
                    </h2>
                    <p class="text-text-secondary mb-6">Geen verborgen kosten. Annuleer wanneer je wilt.</p>

                    <!-- Billing toggle -->
                    <div class="inline-flex items-center gap-3 bg-panel border border-panel-border rounded-xl px-4 py-2">
                        <span :class="['text-sm font-medium', !annualBilling ? 'text-text-primary' : 'text-text-muted']">Maandelijks</span>
                        <button
                            :class="['relative inline-flex h-5 w-9 items-center rounded-full transition-colors', annualBilling ? 'bg-brand' : 'bg-panel-active']"
                            @click="annualBilling = !annualBilling"
                        >
                            <span :class="['inline-block h-3.5 w-3.5 rounded-full bg-white shadow transition-transform', annualBilling ? 'translate-x-4' : 'translate-x-1']" />
                        </button>
                        <span :class="['text-sm font-medium', annualBilling ? 'text-text-primary' : 'text-text-muted']">
                            Jaarlijks
                            <span class="tag tag-profit ml-1 text-xs">-20%</span>
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div
                        v-for="plan in plans"
                        :key="plan.id"
                        :class="['rounded-2xl p-6 relative', plan.highlight ? 'card-glow ring-1 ring-brand/30' : 'card']"
                    >
                        <div v-if="plan.highlight" class="absolute -top-3 left-1/2 -translate-x-1/2">
                            <span class="tag tag-profit text-xs px-3 py-1 shadow-glow-sm">Meest populair</span>
                        </div>

                        <div class="mb-5">
                            <h3 class="text-lg font-bold mb-1">{{ plan.name }}</h3>
                            <p class="text-text-tertiary text-sm mb-3">{{ plan.desc }}</p>
                            <div class="flex items-baseline gap-1">
                                <span class="text-4xl font-black">
                                    €{{ annualBilling && plan.annualPrice ? plan.annualPrice : plan.monthlyPrice }}
                                </span>
                                <span class="text-text-tertiary text-sm">/maand</span>
                            </div>
                            <p v-if="annualBilling && plan.annualPrice" class="text-xs text-text-muted mt-1">
                                Gefactureerd als €{{ (plan.annualPrice * 12).toLocaleString() }}/jaar
                            </p>
                        </div>

                        <ul class="space-y-2.5 mb-6">
                            <li v-for="f in plan.features" :key="f" class="flex items-start gap-2.5">
                                <CheckIcon class="h-4 w-4 text-brand flex-shrink-0 mt-0.5" />
                                <span class="text-sm text-text-secondary">{{ f }}</span>
                            </li>
                        </ul>

                        <Link
                            :href="plan.href"
                            :class="['btn w-full justify-center', plan.highlight ? 'btn-primary' : 'btn-secondary']"
                        >
                            {{ plan.cta }}
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- ─── CTA ─────────────────────────────────────────────────────────── -->
        <section class="py-24 px-6 border-t border-panel-border">
            <div class="max-w-3xl mx-auto text-center">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-brand to-brand-600 flex items-center justify-center mx-auto mb-6 shadow-glow-brand">
                    <span class="text-2xl font-black text-surface">H</span>
                </div>
                <h2 class="text-3xl lg:text-4xl font-black tracking-tight mb-4">
                    Klaar om consistent te worden?
                </h2>
                <p class="text-text-secondary text-lg mb-8">
                    Sluit je aan bij 2,400+ traders die dagelijks Hustle gebruiken om beter te worden.
                </p>
                <Link :href="route('register')" class="btn btn-primary px-10 py-3.5 text-base shadow-glow-brand">
                    Gratis beginnen — geen creditcard nodig
                    <ArrowRightIcon class="h-4 w-4" />
                </Link>
            </div>
        </section>

        <!-- ─── Footer ──────────────────────────────────────────────────────── -->
        <footer class="border-t border-panel-border px-6 py-8">
            <div class="max-w-5xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-brand to-brand-600 flex items-center justify-center">
                        <span class="text-xs font-black text-surface">H</span>
                    </div>
                    <span class="text-sm font-bold">Hustle</span>
                </div>
                <p class="text-xs text-text-muted">© 2026 Hustle Trading. Alle rechten voorbehouden.</p>
                <div class="flex gap-5 text-xs text-text-muted">
                    <a href="#" class="hover:text-text-secondary transition-colors">Privacybeleid</a>
                    <a href="#" class="hover:text-text-secondary transition-colors">Gebruiksvoorwaarden</a>
                    <a href="#" class="hover:text-text-secondary transition-colors">Contact</a>
                </div>
            </div>
        </footer>
    </div>
</template>
