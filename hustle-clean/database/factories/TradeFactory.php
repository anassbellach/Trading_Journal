<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Strategy;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TradeFactory extends Factory
{
    protected $model = Trade::class;

    public function definition(): array
    {
        $user    = User::factory()->create();
        $account = Account::factory()->for($user)->create();

        $instruments = [
            ['ticker' => 'NQ',  'base' => 21000],
            ['ticker' => 'ES',  'base' => 5800],
            ['ticker' => 'GC',  'base' => 3200],
            ['ticker' => 'CL',  'base' => 75],
            ['ticker' => 'YM',  'base' => 42000],
        ];

        $instr     = fake()->randomElement($instruments);
        $direction = fake()->randomElement(['long', 'short']);
        $entry     = $instr['base'] + fake()->numberBetween(-100, 100);
        $slPts     = fake()->numberBetween(8, 25);
        $tpPts     = $slPts * fake()->randomFloat(1, 1.5, 3.5);
        $sl        = $direction === 'long' ? $entry - $slPts : $entry + $slPts;
        $tp        = $direction === 'long' ? $entry + $tpPts : $entry - $tpPts;
        $isWin     = fake()->boolean(60);
        $exitPts   = $isWin ? $tpPts * fake()->randomFloat(2, 0.8, 1.0) : -$slPts;
        $exit      = $direction === 'long' ? $entry + $exitPts : $entry - $exitPts;
        $size      = fake()->numberBetween(1, 3);
        $commission= 4.5 * $size;
        $raw       = ($direction === 'long' ? ($exit - $entry) : ($entry - $exit)) * $size;
        $pnl       = round($raw - $commission, 2);
        $openedAt  = fake()->dateTimeBetween('-90 days', 'now');

        return [
            'account_id'       => $account->id,
            'user_id'          => $user->id,
            'strategy_id'      => null,
            'ticker'           => $instr['ticker'],
            'direction'        => $direction,
            'status'           => 'closed',
            'entry_price'      => $entry,
            'exit_price'       => $exit,
            'stop_loss'        => $sl,
            'take_profit'      => $tp,
            'position_size'    => $size,
            'commission'       => $commission,
            'risk_pct'         => fake()->randomFloat(2, 0.5, 2.0),
            'pnl'              => $pnl,
            'rr_ratio'         => round($exitPts / $slPts, 2),
            'is_win'           => $pnl > 0,
            'session'          => fake()->randomElement(['asian', 'london', 'new_york', 'overnight']),
            'opened_at'        => $openedAt,
            'closed_at'        => (clone $openedAt)->modify('+' . fake()->numberBetween(5, 240) . ' minutes'),
            'duration_seconds' => fake()->numberBetween(300, 14400),
            'psychology_rating'=> fake()->numberBetween(4, 10),
            'notes'            => fake()->optional()->sentence(),
            'mistakes'         => [],
        ];
    }

    public function winning(): static
    {
        return $this->state(fn (array $a) => ['is_win' => true, 'pnl' => abs($a['pnl'] ?? 500)]);
    }

    public function losing(): static
    {
        return $this->state(fn (array $a) => ['is_win' => false, 'pnl' => -abs($a['pnl'] ?? 300)]);
    }
}
