<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Trade extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'account_id', 'user_id', 'strategy_id', 'ticker', 'direction', 'status',
        'entry_price', 'exit_price', 'stop_loss', 'take_profit',
        'position_size', 'commission', 'risk_amount', 'risk_pct',
        'pnl', 'pnl_pct', 'rr_ratio', 'is_win',
        'session', 'opened_at', 'closed_at', 'duration_seconds',
        'psychology_rating', 'psychology_notes', 'mistakes', 'notes',
    ];

    protected $casts = [
        'entry_price'      => 'decimal:5',
        'exit_price'       => 'decimal:5',
        'stop_loss'        => 'decimal:5',
        'take_profit'      => 'decimal:5',
        'position_size'    => 'decimal:4',
        'commission'       => 'decimal:2',
        'risk_amount'      => 'decimal:2',
        'risk_pct'         => 'decimal:2',
        'pnl'              => 'decimal:2',
        'pnl_pct'          => 'decimal:4',
        'rr_ratio'         => 'decimal:4',
        'is_win'           => 'boolean',
        'mistakes'         => 'array',
        'opened_at'        => 'datetime',
        'closed_at'        => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function strategy(): BelongsTo
    {
        return $this->belongsTo(Strategy::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'tag_trade');
    }

    public function screenshots(): HasMany
    {
        return $this->hasMany(Screenshot::class);
    }

    // ─── Computed / Accessors ─────────────────────────────────────────────────

    protected function calculatePnl(): float|null
    {
        if (! $this->exit_price) return null;

        $raw = $this->direction === 'long'
            ? ($this->exit_price - $this->entry_price) * $this->position_size
            : ($this->entry_price - $this->exit_price) * $this->position_size;

        return round($raw - $this->commission, 2);
    }

    protected function calculateRr(): float|null
    {
        if (! $this->stop_loss || ! $this->exit_price) return null;

        $risk   = abs($this->entry_price - $this->stop_loss);
        $reward = $this->direction === 'long'
            ? $this->exit_price - $this->entry_price
            : $this->entry_price - $this->exit_price;

        return $risk > 0 ? round($reward / $risk, 4) : null;
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForAccount(Builder $query, int $accountId): Builder
    {
        return $query->where('account_id', $accountId);
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', 'closed');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    public function scopeInPeriod(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from) $query->where('opened_at', '>=', Carbon::parse($from)->startOfDay());
        if ($to)   $query->where('opened_at', '<=', Carbon::parse($to)->endOfDay());
        return $query;
    }

    public function scopeFiltered(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('ticker', 'like', "%{$search}%")
                  ->orWhereHas('strategy', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        if (! empty($filters['direction'])) $query->where('direction', $filters['direction']);
        if (! empty($filters['session']))   $query->where('session', $filters['session']);
        if (! empty($filters['ticker']))    $query->where('ticker', strtoupper($filters['ticker']));
        if (! empty($filters['strategy_id'])) $query->where('strategy_id', $filters['strategy_id']);

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        } else {
            $query->where('status', 'closed');
        }

        if (! empty($filters['date_from'])) $query->where('opened_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        if (! empty($filters['date_to']))   $query->where('opened_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());

        $sortBy  = $filters['sort_by']  ?? 'opened_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';

        $allowedSorts = ['opened_at', 'closed_at', 'ticker', 'pnl', 'rr_ratio', 'duration_seconds'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        return $query;
    }

    // ─── Boot ─────────────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Trade $trade) {
            // Auto-calculate PnL, RR, is_win, duration on save
            if ($trade->exit_price && $trade->status === 'closed') {
                $trade->pnl     = $trade->calculatePnl();
                $trade->rr_ratio = $trade->calculateRr();
                $trade->is_win   = $trade->pnl !== null && $trade->pnl > 0;

                if ($trade->closed_at && $trade->opened_at) {
                    $trade->duration_seconds = $trade->closed_at->diffInSeconds($trade->opened_at);
                }

                // PnL %
                if ($trade->account?->starting_balance > 0) {
                    $trade->pnl_pct = round(($trade->pnl / $trade->account->starting_balance) * 100, 4);
                }
            }
        });
    }
}
