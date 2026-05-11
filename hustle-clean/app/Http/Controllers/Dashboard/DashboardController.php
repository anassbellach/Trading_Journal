<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Strategy;
use App\Models\Trade;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private readonly AnalyticsService $analytics) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $account = $user->activeAccount();

        if (! $account) {
            return Inertia::render('Dashboard/NoAccount');
        }

        $recentTrades = Trade::where('account_id', $account->id)
            ->where('status', 'closed')
            ->with(['strategy', 'tags'])
            ->orderByDesc('opened_at')
            ->paginate(10);

        $topInsights = $user->aiInsights()
            ->where('is_read', false)
            ->orderByDesc('generated_at')
            ->limit(4)
            ->get();

        return Inertia::render('Dashboard/Index', [
            'stats' => $this->analytics->getDashboardStats($user, $account),
            'equityCurve' => $this->analytics->getEquityCurve($account, '1m'),
            'bySessions' => $this->analytics->getBySession($account),
            'recentTrades' => \App\Http\Resources\Trade\TradeResource::collection($recentTrades),
            'topInsights' => $topInsights,
            'accounts' => $user->accounts,
            'strategies' => Strategy::where('user_id', $user->id)->get(['id', 'name', 'color']),
        ]);
    }
}
