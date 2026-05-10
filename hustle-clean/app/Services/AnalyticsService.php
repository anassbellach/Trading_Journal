<?php

namespace App\Services;

use App\Models\Trade;
use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Build the full dashboard stats for an account.
     */
    public function getDashboardStats(User $user, Account $account, string $period = 'all'): array
    {
        $query = Trade::query()
            ->where('account_id', $account->id)
            ->where('status', 'closed')
            ->when($period !== 'all', function ($q) use ($period) {
                $from = match ($period) {
                    'week'  => now()->subWeek(),
                    'month' => now()->subMonth(),
                    '3m'    => now()->subMonths(3),
                    'ytd'   => now()->startOfYear(),
                    default => null,
                };
                if ($from) $q->where('opened_at', '>=', $from);
            });

        $trades     = $query->get();
        $wins       = $trades->where('is_win', true);
        $losses     = $trades->where('is_win', false);
        $totalPnl   = $trades->sum('pnl');
        $totalWins  = $wins->sum('pnl');
        $totalLoss  = abs($losses->sum('pnl'));
        $avgWin     = $wins->count() > 0 ? $wins->avg('pnl') : 0;
        $avgLoss    = $losses->count() > 0 ? abs($losses->avg('pnl')) : 0;

        return [
            'total_pnl'       => round($totalPnl, 2),
            'total_pnl_pct'   => $account->starting_balance > 0
                                    ? round(($totalPnl / $account->starting_balance) * 100, 2)
                                    : 0,
            'win_rate'        => $trades->count() > 0
                                    ? round(($wins->count() / $trades->count()) * 100, 2)
                                    : 0,
            'profit_factor'   => $totalLoss > 0 ? round($totalWins / $totalLoss, 2) : ($totalWins > 0 ? 999 : 0),
            'avg_rr'          => round($trades->avg('rr_ratio') ?? 0, 2),
            'total_trades'    => $trades->count(),
            'winning_trades'  => $wins->count(),
            'losing_trades'   => $losses->count(),
            'avg_win'         => round($avgWin, 2),
            'avg_loss'        => round($avgLoss, 2),
            'best_trade'      => round($trades->max('pnl') ?? 0, 2),
            'worst_trade'     => round($trades->min('pnl') ?? 0, 2),
            'max_drawdown'    => $this->calculateMaxDrawdown($trades),
            'max_drawdown_pct'=> $account->starting_balance > 0
                                    ? round(($this->calculateMaxDrawdown($trades) / $account->starting_balance) * 100, 2)
                                    : 0,
            'expectancy'      => $trades->count() > 0 ? round($totalPnl / $trades->count(), 2) : 0,
            'commission_paid' => round($trades->sum('commission'), 2),
            'starting_balance'=> (float) $account->starting_balance,
            'current_balance' => (float) $account->current_balance,
            'current_streak'  => $this->calculateStreak($trades),
            'streak_type'     => $this->calculateStreakType($trades),
        ];
    }

    /**
     * Equity curve: daily cumulative PnL.
     */
    public function getEquityCurve(Account $account, string $period = 'all'): array
    {
        $query = Trade::where('account_id', $account->id)
            ->where('status', 'closed')
            ->whereNotNull('pnl')
            ->when($period !== 'all', function ($q) use ($period) {
                $from = match ($period) {
                    '1w' => now()->subWeek(),
                    '1m' => now()->subMonth(),
                    '3m' => now()->subMonths(3),
                    'ytd'=> now()->startOfYear(),
                    default => null,
                };
                if ($from) $q->where('opened_at', '>=', $from);
            })
            ->orderBy('opened_at')
            ->select(
                DB::raw('DATE(opened_at) as date'),
                DB::raw('SUM(pnl) as daily_pnl'),
                DB::raw('COUNT(*) as trade_count')
            )
            ->groupBy('date')
            ->get();

        $runningEquity = (float) $account->starting_balance;
        $result = [];

        foreach ($query as $row) {
            $runningEquity += $row->daily_pnl;
            $result[] = [
                'date'        => $row->date,
                'equity'      => round($runningEquity, 2),
                'pnl'         => round($row->daily_pnl, 2),
                'trade_count' => $row->trade_count,
            ];
        }

        return $result;
    }

    /**
     * Performance breakdown by session.
     */
    public function getBySession(Account $account): array
    {
        $SESSION_LABELS = [
            'asian'      => 'Asian',
            'london'     => 'London',
            'new_york'   => 'New York',
            'overnight'  => 'Overnight',
            'pre_market' => 'Pre-Market',
        ];

        return Trade::where('account_id', $account->id)
            ->where('status', 'closed')
            ->select(
                'session',
                DB::raw('COUNT(*) as trades'),
                DB::raw('SUM(CASE WHEN is_win = 1 THEN 1 ELSE 0 END) as wins'),
                DB::raw('SUM(CASE WHEN is_win = 0 THEN 1 ELSE 0 END) as losses'),
                DB::raw('SUM(pnl) as total_pnl'),
                DB::raw('AVG(pnl) as avg_pnl')
            )
            ->groupBy('session')
            ->get()
            ->map(function ($row) use ($SESSION_LABELS) {
                $winRate = $row->trades > 0 ? round(($row->wins / $row->trades) * 100, 1) : 0;
                return [
                    'session'    => $row->session,
                    'label'      => $SESSION_LABELS[$row->session] ?? $row->session,
                    'trades'     => $row->trades,
                    'wins'       => $row->wins,
                    'losses'     => $row->losses,
                    'win_rate'   => $winRate,
                    'total_pnl'  => round($row->total_pnl, 2),
                    'avg_pnl'    => round($row->avg_pnl, 2),
                ];
            })
            ->toArray();
    }

    /**
     * Performance by day of week.
     */
    public function getByDayOfWeek(Account $account): array
    {
        $days = ['Maandag', 'Dinsdag', 'Woensdag', 'Donderdag', 'Vrijdag', 'Zaterdag', 'Zondag'];

        return Trade::where('account_id', $account->id)
            ->where('status', 'closed')
            ->select(
                DB::raw('DAYOFWEEK(opened_at) as dow'),
                DB::raw('COUNT(*) as trades'),
                DB::raw('SUM(CASE WHEN is_win = 1 THEN 1 ELSE 0 END) as wins'),
                DB::raw('SUM(pnl) as total_pnl')
            )
            ->groupBy('dow')
            ->orderBy('dow')
            ->get()
            ->map(function ($row) use ($days) {
                // MySQL DAYOFWEEK: 1=Sunday
                $idx     = (($row->dow - 2 + 7) % 7); // shift to Mon=0
                $winRate = $row->trades > 0 ? round(($row->wins / $row->trades) * 100, 1) : 0;
                return [
                    'day'       => $idx,
                    'label'     => $days[$idx] ?? "Dag {$idx}",
                    'trades'    => $row->trades,
                    'wins'      => $row->wins,
                    'win_rate'  => $winRate,
                    'total_pnl' => round($row->total_pnl, 2),
                ];
            })
            ->toArray();
    }

    /**
     * Performance by strategy.
     */
    public function getByStrategy(Account $account): array
    {
        return Trade::where('account_id', $account->id)
            ->where('status', 'closed')
            ->select(
                'strategy_id',
                DB::raw('COUNT(*) as trades'),
                DB::raw('SUM(CASE WHEN is_win = 1 THEN 1 ELSE 0 END) as wins'),
                DB::raw('SUM(CASE WHEN is_win = 0 THEN 1 ELSE 0 END) as losses'),
                DB::raw('SUM(pnl) as total_pnl'),
                DB::raw('AVG(pnl) as avg_pnl'),
                DB::raw('AVG(rr_ratio) as avg_rr'),
                DB::raw('SUM(CASE WHEN is_win = 1 THEN pnl ELSE 0 END) as gross_profit'),
                DB::raw('ABS(SUM(CASE WHEN is_win = 0 THEN pnl ELSE 0 END)) as gross_loss')
            )
            ->with('strategy:id,name')
            ->groupBy('strategy_id')
            ->orderByDesc('total_pnl')
            ->get()
            ->map(function ($row) {
                $winRate = $row->trades > 0 ? round(($row->wins / $row->trades) * 100, 1) : 0;
                $pf      = $row->gross_loss > 0 ? round($row->gross_profit / $row->gross_loss, 2) : ($row->gross_profit > 0 ? 999 : 0);
                return [
                    'strategy_id'   => $row->strategy_id,
                    'strategy_name' => $row->strategy?->name ?? 'Geen strategie',
                    'trades'        => $row->trades,
                    'wins'          => $row->wins,
                    'losses'        => $row->losses,
                    'win_rate'      => $winRate,
                    'total_pnl'     => round($row->total_pnl, 2),
                    'avg_pnl'       => round($row->avg_pnl, 2),
                    'avg_rr'        => round($row->avg_rr ?? 0, 2),
                    'profit_factor' => $pf,
                ];
            })
            ->toArray();
    }

    /**
     * RR distribution buckets.
     */
    public function getRrDistribution(Account $account): array
    {
        $trades = Trade::where('account_id', $account->id)
            ->where('status', 'closed')
            ->whereNotNull('rr_ratio')
            ->get(['rr_ratio', 'is_win']);

        $buckets = [
            ['range' => '3R+',     'min' => 3,    'max' => PHP_INT_MAX, 'count' => 0],
            ['range' => '2R – 3R', 'min' => 2,    'max' => 3,          'count' => 0],
            ['range' => '1R – 2R', 'min' => 1,    'max' => 2,          'count' => 0],
            ['range' => '0 – 1R',  'min' => 0,    'max' => 1,          'count' => 0],
            ['range' => 'Verlies', 'min' => -PHP_INT_MAX, 'max' => 0,  'count' => 0],
        ];

        foreach ($trades as $trade) {
            foreach ($buckets as &$bucket) {
                if ($trade->rr_ratio >= $bucket['min'] && $trade->rr_ratio < $bucket['max']) {
                    $bucket['count']++;
                    break;
                }
            }
        }

        return $buckets;
    }

    /**
     * Calendar data: daily PnL map.
     */
    public function getCalendarData(Account $account, int $year, int $month): array
    {
        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $rows = Trade::where('account_id', $account->id)
            ->where('status', 'closed')
            ->whereBetween('opened_at', [$start, $end])
            ->select(
                DB::raw('DATE(opened_at) as date'),
                DB::raw('SUM(pnl) as pnl'),
                DB::raw('COUNT(*) as trades')
            )
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $result = [];
        $current = $start->copy();
        while ($current <= $end) {
            $dateStr = $current->toDateString();
            $row     = $rows[$dateStr] ?? null;
            $isWeekend = $current->isWeekend();

            $result[] = [
                'date'          => $dateStr,
                'pnl'           => $row ? round($row->pnl, 2) : null,
                'trades'        => $row?->trades ?? 0,
                'is_win_day'    => $row ? ($row->pnl > 0) : null,
                'is_trading_day'=> ! $isWeekend && $row !== null,
            ];

            $current->addDay();
        }

        return $result;
    }

    /**
     * Long vs short breakdown.
     */
    public function getLongVsShort(Account $account): array
    {
        return Trade::where('account_id', $account->id)
            ->where('status', 'closed')
            ->select(
                'direction',
                DB::raw('COUNT(*) as trades'),
                DB::raw('SUM(CASE WHEN is_win = 1 THEN 1 ELSE 0 END) as wins'),
                DB::raw('SUM(pnl) as total_pnl')
            )
            ->groupBy('direction')
            ->get()
            ->map(fn ($r) => [
                'direction' => $r->direction,
                'trades'    => $r->trades,
                'win_rate'  => $r->trades > 0 ? round(($r->wins / $r->trades) * 100, 1) : 0,
                'total_pnl' => round($r->total_pnl, 2),
            ])
            ->toArray();
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function calculateMaxDrawdown(Collection $trades): float
    {
        if ($trades->isEmpty()) return 0;

        $peak   = 0;
        $equity = 0;
        $maxDD  = 0;

        foreach ($trades->sortBy('opened_at') as $trade) {
            $equity += $trade->pnl ?? 0;
            if ($equity > $peak) $peak = $equity;
            $dd = $peak - $equity;
            if ($dd > $maxDD) $maxDD = $dd;
        }

        return round($maxDD, 2);
    }

    private function calculateStreak(Collection $trades): int
    {
        $sorted = $trades->sortByDesc('opened_at')->values();
        if ($sorted->isEmpty()) return 0;

        $firstResult = $sorted->first()->is_win;
        $streak = 0;

        foreach ($sorted as $t) {
            if ($t->is_win === $firstResult) $streak++;
            else break;
        }

        return $streak;
    }

    private function calculateStreakType(Collection $trades): ?string
    {
        $latest = $trades->sortByDesc('opened_at')->first();
        if (! $latest) return null;
        return $latest->is_win ? 'win' : 'loss';
    }
}
