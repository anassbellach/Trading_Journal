<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\Trade\TradeResource;
use App\Models\Trade;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    public function __construct(private readonly AnalyticsService $analytics) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $account = $user->activeAccount();

        if (! $account) {
            return Inertia::render('Dashboard/NoAccount');
        }

        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        $selectedDate = $request->get('date');

        $dayTrades = collect();
        if ($selectedDate) {
            $dayTrades = Trade::where('account_id', $account->id)
                ->whereDate('opened_at', $selectedDate)
                ->where('status', 'closed')
                ->with('strategy')
                ->get();
        }

        return Inertia::render('Calendar/Index', [
            'calendarData' => $this->analytics->getCalendarData($account, $year, $month),
            'stats' => $this->analytics->getDashboardStats($user, $account),
            'year' => $year,
            'month' => $month,
            'dayTrades' => TradeResource::collection($dayTrades),
            'selectedDate' => $selectedDate,
        ]);
    }
}
