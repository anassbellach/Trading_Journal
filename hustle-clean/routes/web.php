<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\StripeWebhookController;
use App\Http\Controllers\Dashboard\AiInsightController;
use App\Http\Controllers\Dashboard\AnalyticsController;
use App\Http\Controllers\Dashboard\CalendarController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\SubscriptionController;
use App\Http\Controllers\Dashboard\TradeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => Inertia::render('Landing'))->name('landing');

// Stripe webhook (no CSRF)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->withoutMiddleware(['web', \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('stripe.webhook');

/*
|--------------------------------------------------------------------------
| Auth routes (guests only)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);

    // Google OAuth
    Route::get('/auth/google',          [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Account switching
    Route::post('/accounts/switch', function (\Illuminate\Http\Request $request) {
        $account = \App\Models\Account::where('user_id', $request->user()->id)
            ->findOrFail($request->account_id);
        $request->user()->update(['active_account_id' => $account->id]);
        return redirect()->back();
    })->name('accounts.switch');

    Route::get('/accounts/create', fn () => Inertia::render('Accounts/Create'))->name('accounts.create');
    Route::post('/accounts', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:100'],
            'broker'           => ['nullable', 'string', 'max:100'],
            'type'             => ['required', 'in:live,demo,funded,paper'],
            'currency'         => ['required', 'string', 'size:3'],
            'starting_balance' => ['required', 'numeric', 'min:0'],
        ]);
        $account = \App\Models\Account::create([...$data, 'user_id' => $request->user()->id, 'current_balance' => $data['starting_balance']]);
        $request->user()->update(['active_account_id' => $account->id]);
        return redirect(route('dashboard'))->with('success', 'Account aangemaakt!');
    })->name('accounts.store');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Journal / Trades
    Route::get('/journal',          [TradeController::class, 'index'])->name('journal.index');
    Route::post('/trades',          [TradeController::class, 'store'])->name('trades.store');
    Route::put('/trades/{trade}',   [TradeController::class, 'update'])->name('trades.update');
    Route::delete('/trades/{trade}',[TradeController::class, 'destroy'])->name('trades.destroy');
    Route::get('/trades/export',    [TradeController::class, 'export'])->name('trades.export');

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    // Calendar
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');

    // AI Insights
    Route::get('/ai-insights',                       [AiInsightController::class, 'index'])->name('ai-insights.index');
    Route::post('/ai-insights/generate',             [AiInsightController::class, 'generate'])->name('ai-insights.generate');
    Route::post('/ai-insights/{aiInsight}/read',     [AiInsightController::class, 'markRead'])->name('ai-insights.read');
    Route::post('/ai-insights/read-all',             [AiInsightController::class, 'markAllRead'])->name('ai-insights.read-all');

    // Settings
    Route::get('/settings', fn () => Inertia::render('Settings/Index', [
        'user' => request()->user(),
    ]))->name('settings.index');

    Route::put('/settings/profile', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email,' . $request->user()->id],
            'timezone' => ['required', 'timezone'],
        ]);
        $request->user()->update($request->only('name', 'email', 'timezone'));
        return redirect()->back()->with('success', 'Profiel bijgewerkt.');
    })->name('settings.profile');

    // Subscription / Billing
    Route::get('/subscription',          [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/subscription/checkout',[SubscriptionController::class, 'checkout'])->name('subscription.checkout');
    Route::get('/subscription/portal',   [SubscriptionController::class, 'portal'])->name('subscription.portal');
    Route::get('/subscription/success',  [SubscriptionController::class, 'success'])->name('subscription.success');
});
