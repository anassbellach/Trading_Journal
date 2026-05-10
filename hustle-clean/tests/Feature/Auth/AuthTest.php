<?php

use App\Models\Account;
use App\Models\Trade;
use App\Models\User;

// ─── Auth tests ───────────────────────────────────────────────────────────────

test('login pagina is bereikbaar', function () {
    $response = $this->get('/login');
    $response->assertStatus(200);
    $response->assertInertia(fn ($p) => $p->component('Auth/Login'));
});

test('gebruiker kan inloggen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email'    => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});

test('inloggen mislukt met onjuist wachtwoord', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email'    => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('nieuwe gebruiker kan registreren', function () {
    $response = $this->post('/register', [
        'name'                  => 'Test Trader',
        'email'                 => 'trader@hustle.app',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertDatabaseHas('users', ['email' => 'trader@hustle.app']);

    // Default account created
    $user = User::where('email', 'trader@hustle.app')->first();
    $this->assertDatabaseHas('accounts', ['user_id' => $user->id]);
});

test('uitloggen werkt', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->post('/logout')->assertRedirect('/');
    $this->assertGuest();
});

// ─── Dashboard tests ──────────────────────────────────────────────────────────

test('dashboard laadt voor ingelogde gebruiker', function () {
    $user    = User::factory()->pro()->create();
    $account = Account::factory()->for($user)->create(['is_default' => true]);
    $user->update(['active_account_id' => $account->id]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertInertia(fn ($p) => $p
        ->component('Dashboard/Index')
        ->has('stats')
        ->has('equityCurve')
        ->has('bySessions')
    );
});

test('gast wordt doorgestuurd naar login', function () {
    $this->get('/dashboard')->assertRedirect('/login');
    $this->get('/journal')->assertRedirect('/login');
    $this->get('/analytics')->assertRedirect('/login');
});

// ─── Trade tests ──────────────────────────────────────────────────────────────

test('trade kan worden aangemaakt', function () {
    $user    = User::factory()->pro()->create();
    $account = Account::factory()->for($user)->create(['is_default' => true]);
    $user->update(['active_account_id' => $account->id]);

    $response = $this->actingAs($user)->post('/trades', [
        'account_id'    => $account->id,
        'ticker'        => 'NQ',
        'direction'     => 'long',
        'status'        => 'closed',
        'entry_price'   => 21340,
        'exit_price'    => 21520,
        'stop_loss'     => 21280,
        'position_size' => 1,
        'commission'    => 4.5,
        'session'       => 'new_york',
        'opened_at'     => now()->subHours(3)->format('Y-m-d H:i'),
        'closed_at'     => now()->subHour()->format('Y-m-d H:i'),
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('trades', [
        'ticker'    => 'NQ',
        'user_id'   => $user->id,
        'direction' => 'long',
    ]);

    $trade = Trade::where('user_id', $user->id)->first();
    expect($trade->pnl)->not->toBeNull();
    expect($trade->is_win)->toBeTrue();
});

test('free plan is beperkt tot 50 trades', function () {
    $user    = User::factory()->create(['subscription_plan' => 'free']);
    $account = Account::factory()->for($user)->create(['is_default' => true]);
    $user->update(['active_account_id' => $account->id]);

    // Create 50 trades
    Trade::factory()->count(50)->create([
        'user_id'    => $user->id,
        'account_id' => $account->id,
        'status'     => 'closed',
    ]);

    $response = $this->actingAs($user)->post('/trades', [
        'account_id'    => $account->id,
        'ticker'        => 'ES',
        'direction'     => 'long',
        'entry_price'   => 5800,
        'position_size' => 1,
        'session'       => 'new_york',
        'opened_at'     => now()->format('Y-m-d H:i'),
    ]);

    $response->assertForbidden();
});

test('gebruiker kan alleen eigen trades zien', function () {
    $user1   = User::factory()->pro()->create();
    $user2   = User::factory()->pro()->create();
    $account = Account::factory()->for($user1)->create(['is_default' => true]);
    $user1->update(['active_account_id' => $account->id]);

    $trade = Trade::factory()->create(['user_id' => $user2->id]);

    $this->actingAs($user1)->delete("/trades/{$trade->id}")->assertForbidden();
});

test('trade PnL wordt automatisch berekend', function () {
    $user    = User::factory()->pro()->create();
    $account = Account::factory()->for($user)->create(['is_default' => true]);
    $user->update(['active_account_id' => $account->id]);

    $this->actingAs($user)->post('/trades', [
        'account_id'    => $account->id,
        'ticker'        => 'ES',
        'direction'     => 'long',
        'entry_price'   => 5800.00,
        'exit_price'    => 5850.00,
        'position_size' => 2,
        'commission'    => 4.50,
        'session'       => 'new_york',
        'opened_at'     => now()->subHours(2)->format('Y-m-d H:i'),
        'closed_at'     => now()->format('Y-m-d H:i'),
    ]);

    $trade = Trade::where('user_id', $user->id)->latest()->first();

    // (5850 - 5800) * 2 = 100 - 4.50 commission = 95.50
    expect($trade->pnl)->toBe(95.50);
    expect($trade->is_win)->toBeTrue();
});
