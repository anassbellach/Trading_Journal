<?php

namespace App\Services;

use App\Models\AiInsight;
use App\Models\Trade;
use App\Models\User;
use App\Models\Account;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AiInsightService
{
    public function __construct(
        private readonly AnalyticsService $analytics
    ) {}

    /**
     * Generate all insights for a user's active account.
     */
    public function generateInsights(User $user, Account $account): void
    {
        $trades = Trade::where('account_id', $account->id)
            ->where('status', 'closed')
            ->where('opened_at', '>=', now()->subDays(30))
            ->orderBy('opened_at')
            ->with('strategy')
            ->get();

        if ($trades->count() < 3) return;

        $generated = collect();

        $generated->push(...$this->detectRevengeTrade($trades));
        $generated->push(...$this->detectOvertrading($trades));
        $generated->push(...$this->detectBestEdge($trades, $account));
        $generated->push(...$this->detectRiskViolations($trades, $account));
        $generated->push(...$this->detectPsychologyCorrelation($trades));
        $generated->push($this->generateWeeklySummary($user, $account, $trades));

        foreach ($generated->filter() as $insight) {
            AiInsight::create([
                'user_id'      => $user->id,
                'type'         => $insight['type'],
                'category'     => $insight['category'],
                'title'        => $insight['title'],
                'description'  => $insight['description'],
                'data'         => $insight['data'] ?? [],
                'action_items' => $insight['action_items'] ?? [],
                'severity'     => $insight['severity'],
                'is_read'      => false,
                'generated_at' => now(),
            ]);
        }
    }

    // ─── Pattern Detectors ────────────────────────────────────────────────────

    private function detectRevengeTrade(Collection $trades): array
    {
        $insights = [];
        $sorted   = $trades->sortBy('opened_at')->values();

        $reentries = [];
        for ($i = 1; $i < $sorted->count(); $i++) {
            $prev = $sorted[$i - 1];
            $curr = $sorted[$i];

            if ($prev->is_win === false && $prev->closed_at && $curr->opened_at) {
                $minsBetween = $curr->opened_at->diffInMinutes($prev->closed_at);
                if ($minsBetween <= 15) {
                    $reentries[] = ['prev' => $prev, 'curr' => $curr, 'mins' => $minsBetween];
                }
            }
        }

        if (count($reentries) >= 2) {
            $avgPnlReentry = collect($reentries)->avg(fn ($r) => $r['curr']->pnl ?? 0);
            $insights[] = [
                'type'         => 'psychology',
                'category'     => 'revenge_trading',
                'title'        => 'Revenge Trading Gedetecteerd',
                'description'  => sprintf(
                    'Je bent %d keer binnen 15 minuten na een verlies opnieuw ingestapt. '
                    . 'Gemiddeld PnL bij herintrede: %s$%.2f. Dit patroon duidt op emotioneel handelen.',
                    count($reentries),
                    $avgPnlReentry >= 0 ? '+' : '-',
                    abs($avgPnlReentry)
                ),
                'data'         => ['reentry_count' => count($reentries), 'avg_pnl' => round($avgPnlReentry, 2)],
                'action_items' => [
                    'Voer een 30-minuten afkoelregel in na elk verlies',
                    'Schrijf in je journal hoe je je voelde voordat je opnieuw inging',
                    'Stel een maximum van 1 herintrede per sessie in',
                ],
                'severity' => 'warning',
            ];
        }

        return $insights;
    }

    private function detectOvertrading(Collection $trades): array
    {
        $insights = [];

        $byDay = $trades->groupBy(fn ($t) => Carbon::parse($t->opened_at)->dayOfWeek);

        foreach ($byDay as $dow => $dayTrades) {
            $dayName    = Carbon::now()->startOfWeek()->addDays($dow - 1)->locale('nl')->dayName;
            $tradeCount = $dayTrades->count();
            $winRate    = $dayTrades->count() > 0
                ? round(($dayTrades->where('is_win', true)->count() / $dayTrades->count()) * 100)
                : 0;

            $avgOtherDays = $trades
                ->groupBy(fn ($t) => Carbon::parse($t->opened_at)->dayOfWeek)
                ->filter(fn ($g, $d) => $d !== $dow)
                ->avg(fn ($g) => $g->count());

            if ($tradeCount > ($avgOtherDays * 2.5) && $winRate < 40) {
                $insights[] = [
                    'type'         => 'habit',
                    'category'     => 'overtrading',
                    'title'        => "Overhandelen op {$dayName}",
                    'description'  => sprintf(
                        'Op %s handel je gemiddeld %.1f trades (vs %.1f andere dagen). '
                        . 'Je win rate op %s is slechts %d%%. Overweeg selectiever te zijn.',
                        $dayName, $tradeCount, $avgOtherDays, $dayName, $winRate
                    ),
                    'data'         => ['day' => $dow, 'trades' => $tradeCount, 'win_rate' => $winRate],
                    'action_items' => [
                        "Stel een maximum van {$avgOtherDays} trades op {$dayName}",
                        'Wacht op A+ setups in plaats van elke beweging te handelen',
                        'Log je reden voor elke trade op die dag',
                    ],
                    'severity' => 'warning',
                ];
            }
        }

        return $insights;
    }

    private function detectBestEdge(Collection $trades, Account $account): array
    {
        $insights = [];

        $byStrategy = $trades->groupBy('strategy_id');

        foreach ($byStrategy as $stratId => $stratTrades) {
            if ($stratTrades->count() < 5) continue;

            $winRate = round(($stratTrades->where('is_win', true)->count() / $stratTrades->count()) * 100, 1);
            $avgRr   = round($stratTrades->avg('rr_ratio') ?? 0, 2);
            $totalPnl = round($stratTrades->sum('pnl'), 2);
            $stratName = $stratTrades->first()->strategy?->name ?? 'Onbekend';

            // Only surface it as "best edge" if it's genuinely strong
            if ($winRate >= 65 && $avgRr >= 2.0) {
                $insights[] = [
                    'type'         => 'performance',
                    'category'     => 'best_edge',
                    'title'        => "Sterkste Edge: {$stratName}",
                    'description'  => sprintf(
                        'Je %s strategie heeft een win rate van %s%% en gemiddeld %sR over %d trades. '
                        . 'Totale PnL: +$%s. Dit is je betrouwbaarste edge — focus hierop.',
                        $stratName, $winRate, $avgRr, $stratTrades->count(),
                        number_format($totalPnl, 2)
                    ),
                    'data'         => [
                        'strategy' => $stratName, 'win_rate' => $winRate,
                        'avg_rr' => $avgRr, 'total_pnl' => $totalPnl,
                    ],
                    'action_items' => [
                        "Prioriteer {$stratName} setups boven andere strategieën",
                        'Documenteer exacte entry criteria om consistentie te verbeteren',
                        'Overweeg positiegrootte te verhogen bij A+ {$stratName} setups',
                    ],
                    'severity' => 'positive',
                ];
                break; // Only one best-edge insight
            }
        }

        return $insights;
    }

    private function detectRiskViolations(Collection $trades, Account $account): array
    {
        $insights = [];
        if (! $account->max_daily_loss_pct) return $insights;

        $violations = $trades->filter(function ($t) use ($account) {
            if (! $t->risk_pct) return false;
            return $t->risk_pct > ($account->max_daily_loss_pct * 0.5); // >50% of daily loss limit per trade
        });

        if ($violations->count() >= 2) {
            $insights[] = [
                'type'         => 'risk',
                'category'     => 'risk_alert',
                'title'        => 'Risicobeheer Overtredingen',
                'description'  => sprintf(
                    '%d van je recente trades overschreden je risicodrempel. '
                    . 'Max risico per trade: %.1f%% — jij riskeerde tot %.1f%%. '
                    . 'Inconsistent positiebeheer is de nr. 1 rekeningrover.',
                    $violations->count(),
                    $account->max_daily_loss_pct * 0.5,
                    $violations->max('risk_pct')
                ),
                'data'         => ['violation_count' => $violations->count(), 'max_risk_used' => $violations->max('risk_pct')],
                'action_items' => [
                    'Gebruik een positiegrootte calculator vóór elke trade',
                    'Stel een harde stop in van 1% per trade in je trading plan',
                    'Review elke oversized trade en documenteer waarom',
                ],
                'severity' => 'critical',
            ];
        }

        return $insights;
    }

    private function detectPsychologyCorrelation(Collection $trades): array
    {
        $withRating = $trades->whereNotNull('psychology_rating');
        if ($withRating->count() < 5) return [];

        $highPsych = $withRating->where('psychology_rating', '>=', 8);
        $lowPsych  = $withRating->where('psychology_rating', '<=', 5);

        if ($highPsych->count() < 2 || $lowPsych->count() < 2) return [];

        $highWr = round(($highPsych->where('is_win', true)->count() / $highPsych->count()) * 100);
        $lowWr  = round(($lowPsych->where('is_win', true)->count() / $lowPsych->count()) * 100);

        if (($highWr - $lowWr) >= 25) {
            return [[
                'type'         => 'psychology',
                'category'     => 'pattern',
                'title'        => 'Psychologie Correleert Sterk met Resultaten',
                'description'  => sprintf(
                    'Bij een psychologie score van 8+ heb je een win rate van %d%%. '
                    . 'Bij een score van 5 of lager: slechts %d%%. '
                    . 'Bescherm je mentale staat — het is je scherpste instrument.',
                    $highWr, $lowWr
                ),
                'data'         => ['high_psych_wr' => $highWr, 'low_psych_wr' => $lowWr],
                'action_items' => [
                    'Handel alleen bij een psychologie score van 7 of hoger',
                    'Log je stemming vóór elke sessie in het dagboek',
                    'Sla sessies over wanneer je je niet goed voelt',
                ],
                'severity' => 'positive',
            ]];
        }

        return [];
    }

    private function generateWeeklySummary(User $user, Account $account, Collection $trades): ?array
    {
        $weekTrades = $trades->where('opened_at', '>=', now()->subWeek());
        if ($weekTrades->count() === 0) return null;

        $stats = $this->analytics->getDashboardStats($user, $account, 'week');

        $summary = sprintf(
            '<p>📊 <strong>Performance:</strong> Je handelde %d trades deze week met een win rate van %.1f%% en netto PnL van %s$%.2f.</p>'
            . '<p>🎯 <strong>Beste sessie:</strong> %s.</p>'
            . '<p>🧠 <strong>Psychologie gemiddelde:</strong> %.1f/10.</p>'
            . '<p>📈 <strong>Profit Factor:</strong> %.2f — %s.</p>',
            $weekTrades->count(),
            $stats['win_rate'],
            $stats['total_pnl'] >= 0 ? '+' : '-',
            abs($stats['total_pnl']),
            $weekTrades->sortByDesc('pnl')->first()?->session ?? '—',
            $weekTrades->whereNotNull('psychology_rating')->avg('psychology_rating') ?? 0,
            $stats['profit_factor'],
            $stats['profit_factor'] >= 1.5 ? 'Gezond — blijf consistent' : 'Verbetering nodig'
        );

        return [
            'type'         => 'summary',
            'category'     => 'weekly_summary',
            'title'        => 'Wekelijkse AI Samenvatting',
            'description'  => $summary,
            'data'         => $stats,
            'action_items' => [],
            'severity'     => $stats['total_pnl'] >= 0 ? 'positive' : 'warning',
        ];
    }
}
