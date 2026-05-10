<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AiInsight;
use App\Models\Strategy;
use App\Models\Tag;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Demo user ───────────────────────────────────────────────────────────
        $user = User::factory()->create([
            'name'              => 'Anass H.',
            'email'             => 'demo@hustle.app',
            'password'          => Hash::make('password'),
            'subscription_plan' => 'pro',
        ]);

        // ─── Accounts ────────────────────────────────────────────────────────────
        $account = Account::create([
            'user_id'          => $user->id,
            'name'             => 'Apex NQ Hoofd',
            'broker'           => 'Apex Trader Funding',
            'type'             => 'funded',
            'currency'         => 'USD',
            'starting_balance' => 50000,
            'current_balance'  => 50000,
            'max_daily_loss'   => 1000,
            'max_daily_loss_pct' => 2.0,
            'is_default'       => true,
        ]);

        Account::create([
            'user_id'          => $user->id,
            'name'             => 'Personal Live',
            'broker'           => 'Interactive Brokers',
            'type'             => 'live',
            'currency'         => 'USD',
            'starting_balance' => 25000,
            'current_balance'  => 25000,
            'is_default'       => false,
        ]);

        $user->update(['active_account_id' => $account->id]);

        // ─── Strategies ──────────────────────────────────────────────────────────
        $strategies = collect([
            ['name' => 'Breakout',       'color' => '#00C896', 'description' => 'Handelen op structuurbreuk met momentum bevestiging'],
            ['name' => 'Mean Reversion', 'color' => '#7B9FFF', 'description' => 'Terugkeer naar gemiddelde na extreme beweging'],
            ['name' => 'Trend Follow',   'color' => '#FFB84D', 'description' => 'Meerijden op de hogere timeframe trend'],
            ['name' => 'ICT Reversal',   'color' => '#FF6B8A', 'description' => 'FVG en OB op premium/discount zones'],
            ['name' => 'VWAP Fade',      'color' => '#9B7BFF', 'description' => 'Fade van uitgebreide VWAP afwijkingen'],
        ])->map(fn ($s) => Strategy::create([...$s, 'user_id' => $user->id, 'is_active' => true]));

        // ─── Tags ────────────────────────────────────────────────────────────────
        $tags = collect(['A+ Setup', 'Geduldig', 'Geruwd', 'Plan gevolgd', 'Nieuws'])->map(
            fn ($name) => Tag::create(['user_id' => $user->id, 'name' => $name, 'color' => '#7B9FFF'])
        );

        // ─── Trades (90 days of realistic data) ──────────────────────────────────
        $instruments = [
            ['NQ', 20, 4],    // ticker, point_value, commission
            ['ES', 12.5, 4],
            ['GC', 10, 6],
            ['CL', 1000, 4.5],
            ['YM', 5, 4],
        ];

        $sessions = ['asian', 'london', 'new_york', 'overnight', 'pre_market'];
        $sessionWeights = [0.1, 0.2, 0.5, 0.1, 0.1]; // NY most common

        $now    = now();
        $startDate = $now->copy()->subDays(90);

        $mistakes = ['FOMO entry', 'Moved SL', 'Oversized', 'Revenge trade', 'Early exit'];

        $totalPnl = 0;
        $date     = $startDate->copy();

        while ($date->lte($now)) {
            if ($date->isWeekend()) {
                $date->addDay();
                continue;
            }

            // 70% chance of trading on any given day
            if (rand(1, 100) > 70) {
                $date->addDay();
                continue;
            }

            $tradesThisDay = rand(1, 3);

            for ($t = 0; $t < $tradesThisDay; $t++) {
                [$ticker, $pointValue, $commission] = $instruments[array_rand($instruments)];

                $strategy   = $strategies->random();
                $direction  = rand(0, 1) ? 'long' : 'short';
                $size       = rand(1, 3);

                // Simulate realistic prices
                $entryBase  = match($ticker) {
                    'NQ' => rand(19000, 22000),
                    'ES' => rand(5500, 6200),
                    'GC' => rand(2800, 3400),
                    'CL' => rand(65, 85),
                    'YM' => rand(38000, 44000),
                    default => 1000,
                };
                $entry   = $entryBase + (rand(-50, 50) / 10);
                $slPts   = rand(8, 25);
                $tpPts   = $slPts * (rand(15, 35) / 10); // 1.5R–3.5R TP

                $sl = $direction === 'long' ? $entry - $slPts : $entry + $slPts;
                $tp = $direction === 'long' ? $entry + $tpPts : $entry - $tpPts;

                // 62% win rate for funded account
                $isWin = rand(1, 100) <= 62;
                $exitPts = $isWin
                    ? $tpPts * (rand(80, 100) / 100)   // partial TP
                    : -$slPts * (rand(90, 110) / 100); // past SL sometimes

                $exit = $direction === 'long' ? $entry + $exitPts : $entry - $exitPts;

                $rawPnl  = ($direction === 'long' ? ($exit - $entry) : ($entry - $exit)) * $size;
                $pnl     = round($rawPnl - ($commission * $size), 2);
                $rr      = round($exitPts / $slPts, 2);

                $openedAt = $date->copy()->setHour(rand(8, 17))->setMinute(rand(0, 59));
                $duration = rand(5, 240) * 60; // 5 min to 4 hours in seconds
                $closedAt = $openedAt->copy()->addSeconds($duration);

                Trade::create([
                    'account_id'       => $account->id,
                    'user_id'          => $user->id,
                    'strategy_id'      => rand(0, 3) > 0 ? $strategy->id : null,
                    'ticker'           => $ticker,
                    'direction'        => $direction,
                    'status'           => 'closed',
                    'entry_price'      => $entry,
                    'exit_price'       => $exit,
                    'stop_loss'        => $sl,
                    'take_profit'      => $tp,
                    'position_size'    => $size,
                    'commission'       => $commission * $size,
                    'risk_pct'         => round(($slPts * $size * $pointValue) / 50000 * 100, 2),
                    'pnl'              => $pnl,
                    'rr_ratio'         => $rr,
                    'is_win'           => $pnl > 0,
                    'session'          => $this->weightedRandom($sessions, $sessionWeights),
                    'opened_at'        => $openedAt,
                    'closed_at'        => $closedAt,
                    'duration_seconds' => $duration,
                    'psychology_rating'=> rand(5, 10),
                    'mistakes'         => $isWin ? [] : [fake()->randomElement($mistakes)],
                    'notes'            => rand(0, 1) ? fake()->sentence() : null,
                ]);

                $totalPnl += $pnl;
            }

            $date->addDay();
        }

        // Update account balance
        $account->update(['current_balance' => 50000 + $totalPnl]);

        // ─── AI Insights (seed a few) ────────────────────────────────────────────
        AiInsight::create([
            'user_id'      => $user->id,
            'type'         => 'psychology',
            'category'     => 'revenge_trading',
            'title'        => 'Revenge Trading Gedetecteerd',
            'description'  => 'Je bent 3x binnen 15 minuten na een verlies opnieuw ingestapt. Gemiddeld PnL bij herintrede: -$420.',
            'data'         => ['reentry_count' => 3],
            'action_items' => ['Voer een 30-minuten afkoelregel in', 'Log je emoties na elk verlies'],
            'severity'     => 'warning',
            'is_read'      => false,
            'generated_at' => now()->subHours(2),
        ]);

        AiInsight::create([
            'user_id'      => $user->id,
            'type'         => 'performance',
            'category'     => 'best_edge',
            'title'        => 'Sterkste Edge: Breakout NY',
            'description'  => 'Je Breakout strategie in de NY sessie heeft 72% win rate en gemiddeld 2.8R over 22 trades.',
            'data'         => ['win_rate' => 72, 'avg_rr' => 2.8],
            'action_items' => ['Focus op Breakout in NY sessie', 'Schaal positie bij A+ Breakout'],
            'severity'     => 'positive',
            'is_read'      => false,
            'generated_at' => now()->subHour(),
        ]);

        $this->command->info("✓ Demo user: demo@hustle.app / password");
        $this->command->info("✓ Account: Apex NQ Hoofd met $totalPnl netto PnL over 90 dagen");
    }

    private function weightedRandom(array $items, array $weights): mixed
    {
        $cumulative = [];
        $total      = array_sum($weights);
        $running    = 0;
        foreach ($weights as $i => $w) {
            $running += $w / $total;
            $cumulative[$i] = $running;
        }
        $rand = rand(0, 10000) / 10000;
        foreach ($cumulative as $i => $c) {
            if ($rand <= $c) return $items[$i];
        }
        return $items[0];
    }
}
