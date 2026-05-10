<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Strategy;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

// ─── DashboardController ──────────────────────────────────────────────────────
class DashboardController extends Controller
{
    public function __construct(private readonly AnalyticsService $analytics) {}

    public function index(Request $request): Response
    {
        $user    = $request->user();
        $account = $user->activeAccount();

        if (! $account) {
            return Inertia::render('Dashboard/NoAccount');
        }

        $stats       = $this->analytics->getDashboardStats($user, $account);
        $equityCurve = $this->analytics->getEquityCurve($account, '1m');
        $bySessions  = $this->analytics->getBySession($account);

        $recentTrades = \App\Models\Trade::where('account_id', $account->id)
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
            'stats'        => $stats,
            'equityCurve'  => $equityCurve,
            'bySessions'   => $bySessions,
            'recentTrades' => \App\Http\Resources\Trade\TradeResource::collection($recentTrades),
            'topInsights'  => $topInsights,
            'accounts'     => $user->accounts,
            'strategies'   => Strategy::where('user_id', $user->id)->get(['id', 'name', 'color']),
        ]);
    }
}

// ─── AnalyticsController ──────────────────────────────────────────────────────
namespace App\Http\Controllers\Dashboard;

class AnalyticsController extends Controller
{
    public function __construct(private readonly AnalyticsService $analytics) {}

    public function index(Request $request): Response
    {
        $user    = $request->user();
        $account = $user->activeAccount();

        abort_unless($account, 302, redirect(route('accounts.create')));

        return Inertia::render('Analytics/Index', [
            'analytics' => [
                'stats'           => $this->analytics->getDashboardStats($user, $account),
                'equity_curve'    => $this->analytics->getEquityCurve($account),
                'by_session'      => $this->analytics->getBySession($account),
                'by_day_of_week'  => $this->analytics->getByDayOfWeek($account),
                'by_strategy'     => $this->analytics->getByStrategy($account),
                'rr_distribution' => $this->analytics->getRrDistribution($account),
                'long_vs_short'   => $this->analytics->getLongVsShort($account),
            ],
        ]);
    }
}

// ─── CalendarController ───────────────────────────────────────────────────────
namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;

class CalendarController extends Controller
{
    public function __construct(private readonly AnalyticsService $analytics) {}

    public function index(Request $request): Response
    {
        $user    = $request->user();
        $account = $user->activeAccount();

        abort_unless($account, 302, redirect(route('accounts.create')));

        $year  = (int) $request->get('year',  now()->year);
        $month = (int) $request->get('month', now()->month);
        $selectedDate = $request->get('date');

        $calendarData = $this->analytics->getCalendarData($account, $year, $month);
        $stats        = $this->analytics->getDashboardStats($user, $account);

        $dayTrades = [];
        if ($selectedDate) {
            $dayTrades = \App\Models\Trade::where('account_id', $account->id)
                ->whereDate('opened_at', $selectedDate)
                ->where('status', 'closed')
                ->with('strategy')
                ->get();
        }

        return Inertia::render('Calendar/Index', [
            'calendarData' => $calendarData,
            'stats'        => $stats,
            'year'         => $year,
            'month'        => $month,
            'dayTrades'    => \App\Http\Resources\Trade\TradeResource::collection(collect($dayTrades)),
            'selectedDate' => $selectedDate,
        ]);
    }
}

// ─── AiInsightController ──────────────────────────────────────────────────────
namespace App\Http\Controllers\Dashboard;

use App\Jobs\GenerateAiInsightsJob;
use App\Models\AiInsight;
use App\Services\AiInsightService;

class AiInsightController extends Controller
{
    public function __construct(private readonly AiInsightService $insightService) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        $insights = $user->aiInsights()
            ->orderByDesc('generated_at')
            ->get();

        $weeklySummary = $user->aiInsights()
            ->where('category', 'weekly_summary')
            ->orderByDesc('generated_at')
            ->first();

        return Inertia::render('AiInsights/Index', [
            'insights'        => $insights,
            'weeklySummary'   => $weeklySummary?->description,
            'lastGeneratedAt' => $insights->max('generated_at'),
            'unread'          => $user->unreadInsightsCount(),
        ]);
    }

    public function generate(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user    = $request->user();
        $account = $user->activeAccount();

        // Dispatch as queue job (non-blocking)
        GenerateAiInsightsJob::dispatch($user->id, $account->id);

        return redirect()->back()->with('success', 'AI analyse gestart. Vernieuwen over een moment.');
    }

    public function markRead(Request $request, AiInsight $aiInsight): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('update', $aiInsight);
        $aiInsight->update(['is_read' => true]);
        return redirect()->back();
    }

    public function markAllRead(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->user()->aiInsights()->where('is_read', false)->update(['is_read' => true]);
        return redirect()->back()->with('success', 'Alle inzichten gelezen.');
    }
}
