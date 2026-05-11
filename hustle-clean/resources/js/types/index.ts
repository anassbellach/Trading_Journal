// ─── Core Domain Types ──────────────────────────────────────────────────────

export interface User {
    id: number
    name: string
    email: string
    avatar: string | null
    email_verified_at: string | null
    subscription_plan: SubscriptionPlan
    subscription_status: SubscriptionStatus
    created_at: string
}

export interface Account {
    id: number
    user_id: number
    name: string
    broker: string
    type: AccountType
    currency: string
    starting_balance: number
    current_balance: number
    max_daily_loss: number | null
    max_daily_loss_pct: number | null
    trades_count: number
    is_default: boolean
    created_at: string
}

export type AccountType = 'live' | 'demo' | 'funded' | 'paper'

export interface Trade {
    id: number
    account_id: number
    account: Account
    ticker: string
    direction: TradeDirection
    status: TradeStatus
    entry_price: number
    exit_price: number | null
    stop_loss: number | null
    take_profit: number | null
    position_size: number
    commission: number
    risk_amount: number | null
    risk_pct: number | null
    pnl: number | null
    pnl_pct: number | null
    rr_ratio: number | null
    session: Session
    strategy_id: number | null
    strategy: Strategy | null
    tags: Tag[]
    screenshots: Screenshot[]
    opened_at: string
    closed_at: string | null
    duration_seconds: number | null
    psychology_rating: number | null
    psychology_notes: string | null
    mistakes: string[]
    notes: string | null
    is_win: boolean | null
    created_at: string
    updated_at: string
}

export type TradeDirection = 'long' | 'short'
export type TradeStatus    = 'open' | 'closed' | 'cancelled'
export type Session        = 'asian' | 'london' | 'new_york' | 'overnight' | 'pre_market'

export interface Strategy {
    id: number
    user_id: number
    name: string
    description: string | null
    color: string
    trades_count: number
    win_rate: number | null
    avg_rr: number | null
    total_pnl: number
    is_active: boolean
}

export interface Tag {
    id: number
    user_id: number
    name: string
    color: string
    trades_count: number
}

export interface Screenshot {
    id: number
    trade_id: number
    path: string
    url: string
    type: 'entry' | 'exit' | 'analysis'
    notes: string | null
}

export interface JournalEntry {
    id: number
    user_id: number
    date: string
    mood: number
    market_bias: 'bullish' | 'bearish' | 'neutral'
    pre_session_notes: string | null
    post_session_notes: string | null
    lessons_learned: string | null
}

export interface AiInsight {
    id: number
    user_id: number
    type: InsightType
    category: InsightCategory
    title: string
    description: string
    data: Record<string, unknown>
    action_items: string[]
    severity: 'info' | 'warning' | 'critical' | 'positive'
    is_read: boolean
    generated_at: string
}

export type InsightType     = 'habit' | 'performance' | 'psychology' | 'risk' | 'summary'
export type InsightCategory = 'revenge_trading' | 'overtrading' | 'best_edge' | 'risk_alert' | 'weekly_summary' | 'pattern'

export interface Goal {
    id: number
    user_id: number
    title: string
    type: 'pnl' | 'win_rate' | 'profit_factor' | 'trades' | 'rr' | 'streak'
    target_value: number
    current_value: number
    period: 'daily' | 'weekly' | 'monthly' | 'yearly'
    start_date: string
    end_date: string | null
    is_completed: boolean
    progress_pct: number
}

// ─── Analytics / Dashboard Types ────────────────────────────────────────────

export interface DashboardStats {
    total_pnl: number
    total_pnl_pct: number
    win_rate: number
    profit_factor: number
    avg_rr: number
    total_trades: number
    winning_trades: number
    losing_trades: number
    avg_win: number
    avg_loss: number
    best_trade: number
    worst_trade: number
    current_streak: number
    streak_type: 'win' | 'loss' | null
    max_drawdown: number
    max_drawdown_pct: number
    expectancy: number
    commission_paid: number
    starting_balance: number
    current_balance: number
}

export interface EquityCurvePoint {
    date: string
    equity: number
    pnl: number
    trade_count: number
}

export interface PerformanceBySession {
    session: Session
    label: string
    trades: number
    wins: number
    losses: number
    win_rate: number
    total_pnl: number
    avg_pnl: number
}

export interface PerformanceByDay {
    day: number
    label: string
    trades: number
    wins: number
    win_rate: number
    total_pnl: number
}

export interface CalendarDay {
    date: string
    pnl: number | null
    trades: number
    is_win_day: boolean | null
    is_trading_day: boolean
}

export interface RrBucket {
    range: string
    min: number
    max: number
    count: number
}

export interface AnalyticsData {
    stats: DashboardStats
    equity_curve: EquityCurvePoint[]
    by_session: PerformanceBySession[]
    by_day_of_week: PerformanceByDay[]
    by_strategy: StrategyPerformance[]
    rr_distribution: RrBucket[]
    calendar: CalendarDay[]
    long_vs_short: { direction: string; trades: number; win_rate: number; total_pnl: number }[]
}

export interface StrategyPerformance {
    strategy_id: number | null
    strategy_name: string
    trades: number
    wins: number
    losses: number
    win_rate: number
    total_pnl: number
    avg_pnl: number
    avg_rr: number
    profit_factor: number
}

// ─── Subscription ────────────────────────────────────────────────────────────

export type SubscriptionPlan   = 'free' | 'pro' | 'premium'
export type SubscriptionStatus = 'active' | 'trialing' | 'past_due' | 'canceled' | 'incomplete'

export interface SubscriptionDetails {
    plan: SubscriptionPlan
    status: SubscriptionStatus
    current_period_end: string | null
    cancel_at_period_end: boolean
    stripe_customer_id: string | null
}

// ─── Pagination / API ────────────────────────────────────────────────────────

export interface PaginatedResponse<T> {
    data: T[]
    meta: {
        current_page: number
        from: number
        last_page: number
        per_page: number
        to: number
        total: number
    }
    links: {
        first: string
        last: string
        prev: string | null
        next: string | null
    }
}

export interface ApiError {
    message: string
    errors?: Record<string, string[]>
}

// ─── Filters ─────────────────────────────────────────────────────────────────

export interface TradeFilters {
    search?: string
    direction?: TradeDirection | ''
    session?: Session | ''
    strategy_id?: number | ''
    status?: TradeStatus | ''
    date_from?: string
    date_to?: string
    ticker?: string
    is_win?: boolean | ''
    sort_by?: string
    sort_dir?: 'asc' | 'desc'
    per_page?: number
    page?: number
}

// ─── Inertia Shared Props ────────────────────────────────────────────────────

export interface SharedData {
    [key: string]: unknown
    auth: {
        user: User | null
    }
    activeAccount: Account | null
    accounts: Account[]
    flash: {
        success?: string
        error?: string
        info?: string
    }
    unread_insights: number
}
