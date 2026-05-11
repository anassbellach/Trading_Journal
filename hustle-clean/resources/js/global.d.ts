/// <reference types="vite/client" />

import type { route as ziggyRoute } from 'ziggy-js'

type RouteHelper = typeof ziggyRoute

declare global {
    const route: RouteHelper
}

declare module '@vue/runtime-core' {
    interface ComponentCustomProperties {
        route: RouteHelper
    }
}

declare module 'vue' {
    interface ComponentCustomProperties {
        route: RouteHelper
    }
}

export {}
