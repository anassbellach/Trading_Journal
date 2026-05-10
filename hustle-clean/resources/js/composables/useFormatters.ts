/**
 * Shared formatting utilities for trade data.
 */
export function useFormatters() {

    function formatPnl(value: number | null, showSign = true): string {
        if (value === null) return '—'
        const abs = Math.abs(value).toLocaleString('nl-NL', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        if (!showSign) return `$${abs}`
        return value >= 0 ? `+$${abs}` : `-$${abs}`
    }

    function formatPnlColor(value: number | null): string {
        if (value === null) return 'text-text-secondary'
        return value >= 0 ? 'text-profit' : 'text-loss'
    }

    function formatRr(value: number | null): string {
        if (value === null) return '—'
        return (value >= 0 ? '+' : '') + value.toFixed(2) + 'R'
    }

    function formatPercent(value: number | null, decimals = 1): string {
        if (value === null) return '—'
        return (value >= 0 ? '+' : '') + value.toFixed(decimals) + '%'
    }

    function formatDuration(seconds: number | null): string {
        if (!seconds) return '—'
        const h = Math.floor(seconds / 3600)
        const m = Math.floor((seconds % 3600) / 60)
        if (h > 0) return `${h}u ${m}m`
        return `${m}m`
    }

    function formatDate(dt: string | null | undefined, format: 'short' | 'long' | 'time' = 'short'): string {
        if (!dt) return '—'
        const d = new Date(dt)
        if (format === 'short') return d.toLocaleDateString('nl-NL', { day: '2-digit', month: 'short', year: '2-digit' })
        if (format === 'long')  return d.toLocaleDateString('nl-NL', { day: 'numeric', month: 'long', year: 'numeric' })
        if (format === 'time')  return d.toLocaleTimeString('nl-NL', { hour: '2-digit', minute: '2-digit' })
        return dt
    }

    function formatBalance(value: number, currency = 'USD'): string {
        return new Intl.NumberFormat('nl-NL', {
            style: 'currency',
            currency,
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(value)
    }

    return {
        formatPnl,
        formatPnlColor,
        formatRr,
        formatPercent,
        formatDuration,
        formatDate,
        formatBalance,
    }
}
