import { createApp, h, DefineComponent } from 'vue'
import { createInertiaApp, Link, Head } from '@inertiajs/vue3'
import { createPinia } from 'pinia'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import VueApexCharts from 'vue-apexcharts'
import { ZiggyVue } from 'ziggy-js'
import '../css/app.css'

const pinia = createPinia()

createInertiaApp({
    title: (title) => title ? `${title} · Hustle` : 'Hustle — Trading Journal',
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue')
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(ZiggyVue)
            .use(VueApexCharts)
            .component('Link', Link)
            .component('Head', Head)
            .mount(el)
    },
    progress: {
        color: '#00C896',
        showSpinner: false,
    },
})
