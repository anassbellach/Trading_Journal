<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TradeService
{
    /**
     * Create a new trade and attach tags/screenshots.
     */
    public function createTrade(User $user, array $data): Trade
    {
        return DB::transaction(function () use ($user, $data) {
            $trade = Trade::create([
                'user_id'          => $user->id,
                'account_id'       => $data['account_id'],
                'strategy_id'      => $data['strategy_id'] ?: null,
                'ticker'           => strtoupper($data['ticker']),
                'direction'        => $data['direction'],
                'status'           => $data['status'] ?? 'closed',
                'entry_price'      => $data['entry_price'],
                'exit_price'       => $data['exit_price'] ?? null,
                'stop_loss'        => $data['stop_loss'] ?? null,
                'take_profit'      => $data['take_profit'] ?? null,
                'position_size'    => $data['position_size'],
                'commission'       => $data['commission'] ?? 0,
                'risk_pct'         => $data['risk_pct'] ?? null,
                'session'          => $data['session'],
                'opened_at'        => $data['opened_at'],
                'closed_at'        => $data['closed_at'] ?? null,
                'psychology_rating'=> $data['psychology_rating'] ?? null,
                'psychology_notes' => $data['psychology_notes'] ?? null,
                'mistakes'         => $data['mistakes'] ?? [],
                'notes'            => $data['notes'] ?? null,
            ]);

            // Sync tags
            if (! empty($data['tags'])) {
                $trade->tags()->sync($data['tags']);
            }

            // Handle screenshots
            if (! empty($data['screenshots'])) {
                foreach ($data['screenshots'] as $file) {
                    $this->attachScreenshot($trade, $file);
                }
            }

            // Update account balance
            $this->syncAccountBalance($trade->account);

            return $trade->load(['strategy', 'tags', 'screenshots']);
        });
    }

    /**
     * Update an existing trade.
     */
    public function updateTrade(Trade $trade, array $data): Trade
    {
        return DB::transaction(function () use ($trade, $data) {
            $trade->update([
                'account_id'       => $data['account_id'] ?? $trade->account_id,
                'strategy_id'      => $data['strategy_id'] ?: null,
                'ticker'           => strtoupper($data['ticker'] ?? $trade->ticker),
                'direction'        => $data['direction'] ?? $trade->direction,
                'status'           => $data['status'] ?? $trade->status,
                'entry_price'      => $data['entry_price'] ?? $trade->entry_price,
                'exit_price'       => $data['exit_price'] ?? null,
                'stop_loss'        => $data['stop_loss'] ?? null,
                'take_profit'      => $data['take_profit'] ?? null,
                'position_size'    => $data['position_size'] ?? $trade->position_size,
                'commission'       => $data['commission'] ?? $trade->commission,
                'risk_pct'         => $data['risk_pct'] ?? null,
                'session'          => $data['session'] ?? $trade->session,
                'opened_at'        => $data['opened_at'] ?? $trade->opened_at,
                'closed_at'        => $data['closed_at'] ?? null,
                'psychology_rating'=> $data['psychology_rating'] ?? $trade->psychology_rating,
                'psychology_notes' => $data['psychology_notes'] ?? $trade->psychology_notes,
                'mistakes'         => $data['mistakes'] ?? $trade->mistakes,
                'notes'            => $data['notes'] ?? $trade->notes,
            ]);

            if (isset($data['tags'])) {
                $trade->tags()->sync($data['tags']);
            }

            $this->syncAccountBalance($trade->account);

            return $trade->refresh()->load(['strategy', 'tags', 'screenshots']);
        });
    }

    /**
     * Delete a trade (soft delete).
     */
    public function deleteTrade(Trade $trade): void
    {
        DB::transaction(function () use ($trade) {
            $account = $trade->account;
            $trade->delete();
            $this->syncAccountBalance($account);
        });
    }

    /**
     * Bulk import trades from CSV (broker export).
     */
    public function importFromCsv(User $user, Account $account, string $csvPath, string $broker): array
    {
        $importer = app("App\\Services\\Importers\\{$broker}Importer");
        return $importer->import($user, $account, $csvPath);
    }

    /**
     * Export trades to CSV.
     */
    public function exportToCsv(User $user, array $filters): string
    {
        $trades = Trade::where('user_id', $user->id)
            ->filtered($filters)
            ->with(['strategy', 'tags', 'account'])
            ->get();

        $rows = [
            ['ID', 'Ticker', 'Richting', 'Account', 'Strategie', 'Entry', 'Exit', 'SL', 'TP', 'Grootte', 'Commissie', 'PnL', 'RR', 'Sessie', 'Geopend', 'Gesloten', 'Duur (min)', 'Psychologie', 'Notes'],
        ];

        foreach ($trades as $trade) {
            $rows[] = [
                $trade->id,
                $trade->ticker,
                $trade->direction,
                $trade->account->name,
                $trade->strategy?->name ?? '',
                $trade->entry_price,
                $trade->exit_price ?? '',
                $trade->stop_loss ?? '',
                $trade->take_profit ?? '',
                $trade->position_size,
                $trade->commission,
                $trade->pnl ?? '',
                $trade->rr_ratio ?? '',
                $trade->session,
                $trade->opened_at?->format('Y-m-d H:i'),
                $trade->closed_at?->format('Y-m-d H:i') ?? '',
                $trade->duration_seconds ? round($trade->duration_seconds / 60) : '',
                $trade->psychology_rating ?? '',
                str_replace(["\n", "\r"], ' ', $trade->notes ?? ''),
            ];
        }

        $filename = 'hustle_trades_' . now()->format('Ymd_His') . '.csv';
        $path     = "exports/{$filename}";

        $csv = '';
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn ($v) => '"' . str_replace('"', '""', $v) . '"', $row)) . "\n";
        }

        Storage::put($path, $csv);
        return Storage::path($path);
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function attachScreenshot(Trade $trade, UploadedFile $file): void
    {
        $path = $file->store("screenshots/{$trade->user_id}/{$trade->id}", 'public');
        $trade->screenshots()->create([
            'path' => $path,
            'size' => $file->getSize(),
        ]);
    }

    private function syncAccountBalance(Account $account): void
    {
        $totalPnl = Trade::where('account_id', $account->id)
            ->where('status', 'closed')
            ->sum('pnl');

        $account->update([
            'current_balance' => $account->starting_balance + $totalPnl,
        ]);
    }
}
