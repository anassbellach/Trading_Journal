<?php

use App\Models\Account;
use App\Models\Trade;
use App\Models\User;
use App\Services\AnalyticsService;

// ─── AnalyticsService unit tests ─────────────────────────────────────────────

test('getDashboardStats berekent correcte win rate', function () {
    $user    = User::factory()->pro()->create();
    $account = Account::factory()->for($user)->create(['starting_balance' => 50000, 'current_balance' => 50000]);

    // 3 wins, 2 losses
    Trade::factory()->count(3)->create(['user_id' => $user->id, 'account_id' => $account->id, 'status' => 'closed', 'is_win' => true,  'pnl' => 500]);
    Trade::factory()->count(2)->create(['user_id' => $user->id, 'account_id' => $account->id, 'status' => 'closed', 'is_win' => false, 'pnl' => -200]);

    $service = app(AnalyticsService::class);
    $stats   = $service->getDashboardStats($user, $account);

    expect($stats['win_rate'])->toBe(60.0);
    expect($stats['total_trades'])->toBe(5);
    expect($stats['winning_trades'])->toBe(3);
    expect($stats['losing_trades'])->toBe(2);
    expect($stats['total_pnl'])->toBe(1100.0);
});

test('getDashboardStats berekent correcte profit factor', function () {
    $user    = User::factory()->create();
    $account = Account::factory()->for($user)->create(['starting_balance' => 50000]);

    // Gross profit: 3000, Gross loss: 1000 -> PF = 3.0
    Trade::factory()->count(3)->create(['user_id' => $user->id, 'account_id' => $account->id, 'status' => 'closed', 'is_win' => true,  'pnl' => 1000]);
    Trade::factory()->count(2)->create(['user_id' => $user->id, 'account_id' => $account->id, 'status' => 'closed', 'is_win' => false, 'pnl' => -500]);

    $stats = app(AnalyticsService::class)->getDashboardStats(User::find($user->id), $account);

    expect($stats['profit_factor'])->toBe(3.0);
});

test('getEquityCurve bouwt cumulatieve equity op', function () {
    $user    = User::factory()->create();
    $account = Account::factory()->for($user)->create(['starting_balance' => 10000, 'current_balance' => 10000]);

    Trade::factory()->create(['user_id' => $user->id, 'account_id' => $account->id, 'status' => 'closed', 'pnl' => 500,  'opened_at' => now()->subDays(2)]);
    Trade::factory()->create(['user_id' => $user->id, 'account_id' => $account->id, 'status' => 'closed', 'pnl' => -200, 'opened_at' => now()->subDay()]);
    Trade::factory()->create(['user_id' => $user->id, 'account_id' => $account->id, 'status' => 'closed', 'pnl' => 300,  'opened_at' => now()]);

    $curve = app(AnalyticsService::class)->getEquityCurve($account);

    expect($curve)->toHaveCount(3);
    expect($curve[0]['equity'])->toBe(10500.0);
    expect($curve[1]['equity'])->toBe(10300.0);
    expect($curve[2]['equity'])->toBe(10600.0);
});

test('calculateMaxDrawdown vindt juiste drawdown', function () {
    $user    = User::factory()->create();
    $account = Account::factory()->for($user)->create(['starting_balance' => 10000]);

    // Equity goes: +500, +300, -800, -200, +400
    // Peak = 800, then drops 1000 -> max DD = 1000
    $pnls = [500, 300, -800, -200, 400];
    foreach ($pnls as $i => $pnl) {
        Trade::factory()->create([
            'user_id'    => $user->id,
            'account_id' => $account->id,
            'status'     => 'closed',
            'pnl'        => $pnl,
            'opened_at'  => now()->subDays(5 - $i),
        ]);
    }

    $stats = app(AnalyticsService::class)->getDashboardStats(User::find($user->id), $account);
    expect($stats['max_drawdown'])->toBe(1000.0);
});
