<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'remember_token'    => Str::random(10),
            'subscription_plan' => 'free',
            'timezone'          => 'Europe/Amsterdam',
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $a) => ['email_verified_at' => null]);
    }

    public function pro(): static
    {
        return $this->state(fn (array $a) => ['subscription_plan' => 'pro']);
    }

    public function premium(): static
    {
        return $this->state(fn (array $a) => ['subscription_plan' => 'premium']);
    }
}
