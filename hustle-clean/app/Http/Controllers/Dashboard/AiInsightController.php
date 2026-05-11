<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateAiInsightsJob;
use App\Models\AiInsight;
use App\Services\AiInsightService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

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
            'insights' => $insights,
            'weeklySummary' => $weeklySummary?->description,
            'lastGeneratedAt' => $insights->max('generated_at'),
            'unread' => $user->unreadInsightsCount(),
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $user = $request->user();
        $account = $user->activeAccount();

        if (! $account) {
            return redirect(route('accounts.create'));
        }

        GenerateAiInsightsJob::dispatch($user->id, $account->id);

        return redirect()->back()->with('success', 'AI analyse gestart. Vernieuwen over een moment.');
    }

    public function markRead(Request $request, AiInsight $aiInsight): RedirectResponse
    {
        abort_unless($aiInsight->user_id === $request->user()->id, 403);
        $aiInsight->update(['is_read' => true]);

        return redirect()->back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->aiInsights()->where('is_read', false)->update(['is_read' => true]);

        return redirect()->back()->with('success', 'Alle inzichten gelezen.');
    }
}
