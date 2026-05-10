<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        $balance = fake()->randomFloat(2, 10000, 100000);

        return [
            'user_id'          => User::factory(),
            'name'             => fake()->randomElement(['Apex NQ', 'FTMO ES', 'Personal Live', 'Demo Account']),
            'broker'           => fake()->randomElement(['Apex Trader Funding', 'FTMO', 'Interactive Brokers', 'TopStep', 'NinjaTrader']),
            'type'             => fake()->randomElement(['live', 'demo', 'funded', 'paper']),
            'currency'         => 'USD',
            'starting_balance' => $balance,
            'current_balance'  => $balance,
            'max_daily_loss'   => $balance * 0.02,
            'max_daily_loss_pct' => 2.0,
            'is_default'       => false,
            'is_active'        => true,
        ];
    }

    public function funded(): static
    {
        return $this->state(fn (array $a) => [
            'type'   => 'funded',
            'broker' => fake()->randomElement(['Apex Trader Funding', 'FTMO', 'TopStep']),
        ]);
    }

    public function demo(): static
    {
        return $this->state(fn (array $a) => ['type' => 'demo']);
    }
}
