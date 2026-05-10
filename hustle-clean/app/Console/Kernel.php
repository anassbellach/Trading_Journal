<?php

namespace App\Console;

use App\Jobs\GenerateAiInsightsJob;
use App\Models\Account;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Generate weekly AI summaries every Sunday at 20:00
        $schedule->call(function () {
            Account::with('user')
                ->where('is_active', true)
                ->each(function (Account $account) {
                    GenerateAiInsightsJob::dispatch($account->user_id, $account->id)
                        ->delay(now()->addSeconds(rand(0, 300))); // stagger
                });
        })->weeklyOn(0, '20:00')->name('weekly-ai-insights')->withoutOverlapping();

        // Clean up old export files daily
        $schedule->command('storage:clean-exports')->daily();

        // Prune old notifications
        $schedule->command('notifications:prune')->weekly();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
