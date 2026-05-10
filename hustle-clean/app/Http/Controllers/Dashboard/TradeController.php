<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Trade\StoreTradeRequest;
use App\Http\Resources\Trade\TradeResource;
use App\Models\Strategy;
use App\Models\Trade;
use App\Services\AnalyticsService;
use App\Services\TradeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TradeController extends Controller
{
    public function __construct(
        private readonly TradeService     $tradeService,
        private readonly AnalyticsService $analyticsService,
    ) {}

    /**
     * Journal index — paginated, filtered trade list.
     */
    public function index(Request $request): Response
    {
        $user    = $request->user();
        $account = $user->activeAccount();

        abort_unless($account, 302, redirect(route('accounts.create')));

        $filters = $request->only([
            'search', 'direction', 'session', 'strategy_id',
            'status', 'date_from', 'date_to', 'sort_by', 'sort_dir', 'per_page',
        ]);
        $filters['status'] = $filters['status'] ?? 'closed';

        $trades = Trade::where('account_id', $account->id)
            ->filtered($filters)
            ->with(['strategy', 'tags', 'account'])
            ->paginate($filters['per_page'] ?? 25)
            ->withQueryString();

        $stats = $this->analyticsService->getDashboardStats($user, $account);

        return Inertia::render('Journal/Index', [
            'trades'     => TradeResource::collection($trades),
            'stats'      => $stats,
            'strategies' => Strategy::where('user_id', $user->id)->active()->get(['id', 'name', 'color']),
            'accounts'   => $user->accounts,
            'filters'    => $filters,
        ]);
    }

    /**
     * Store a new trade.
     */
    public function store(StoreTradeRequest $request): RedirectResponse
    {
        $this->authorize('create', Trade::class);

        $trade = $this->tradeService->createTrade($request->user(), $request->validated());

        return redirect()->back()->with('success', "Trade {$trade->ticker} succesvol gelogd.");
    }

    /**
     * Update an existing trade.
     */
    public function update(StoreTradeRequest $request, Trade $trade): RedirectResponse
    {
        $this->authorize('update', $trade);

        $this->tradeService->updateTrade($trade, $request->validated());

        return redirect()->back()->with('success', 'Trade bijgewerkt.');
    }

    /**
     * Delete a trade.
     */
    public function destroy(Request $request, Trade $trade): RedirectResponse
    {
        $this->authorize('delete', $trade);

        $this->tradeService->deleteTrade($trade);

        return redirect()->back()->with('success', 'Trade verwijderd.');
    }

    /**
     * Export trades to CSV.
     */
    public function export(Request $request): BinaryFileResponse
    {
        $path = $this->tradeService->exportToCsv(
            $request->user(),
            $request->only(['search', 'direction', 'session', 'strategy_id', 'status', 'date_from', 'date_to'])
        );

        return response()->download($path, 'hustle_trades.csv', [
            'Content-Type' => 'text/csv',
        ])->deleteFileAfterSend();
    }
}
