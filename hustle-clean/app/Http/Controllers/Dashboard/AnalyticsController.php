<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function __construct(private readonly AnalyticsService $analytics) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $account = $user->activeAccount();

        if (! $account) {
            return Inertia::render('Dashboard/NoAccount');
        }

        return Inertia::render('Analytics/Index', [
            'analytics' => [
                'stats' => $this->analytics->getDashboardStats($user, $account),
                'equity_curve' => $this->analytics->getEquityCurve($account),
                'by_session' => $this->analytics->getBySession($account),
                'by_day_of_week' => $this->analytics->getByDayOfWeek($account),
                'by_strategy' => $this->analytics->getByStrategy($account),
                'rr_distribution' => $this->analytics->getRrDistribution($account),
                'long_vs_short' => $this->analytics->getLongVsShort($account),
            ],
        ]);
    }
}
