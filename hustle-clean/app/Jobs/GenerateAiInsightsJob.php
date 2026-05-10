<?php

namespace App\Jobs;

use App\Models\Account;
use App\Models\User;
use App\Services\AiInsightService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateAiInsightsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;
    public int $tries   = 2;

    public function __construct(
        private readonly int $userId,
        private readonly int $accountId,
    ) {}

    public function handle(AiInsightService $insightService): void
    {
        $user    = User::find($this->userId);
        $account = Account::find($this->accountId);

        if (! $user || ! $account) {
            Log::warning("GenerateAiInsightsJob: user/account not found", [
                'user_id'    => $this->userId,
                'account_id' => $this->accountId,
            ]);
            return;
        }

        // Prevent spamming: only generate if last generation was > 1 hour ago
        $lastInsight = $user->aiInsights()->orderByDesc('generated_at')->first();
        if ($lastInsight && $lastInsight->generated_at->gt(now()->subHour())) {
            Log::info("GenerateAiInsightsJob: skipped (too recent)", ['user_id' => $this->userId]);
            return;
        }

        Log::info("GenerateAiInsightsJob: generating for user {$this->userId}");
        $insightService->generateInsights($user, $account);
        Log::info("GenerateAiInsightsJob: done for user {$this->userId}");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("GenerateAiInsightsJob: failed", [
            'user_id' => $this->userId,
            'error'   => $exception->getMessage(),
        ]);
    }
}
