<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiInsight extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'type', 'category', 'title', 'description',
        'data', 'action_items', 'severity', 'is_read', 'generated_at',
    ];

    protected $casts = [
        'data'         => 'array',
        'action_items' => 'array',
        'is_read'      => 'boolean',
        'generated_at' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}

// ─── Subscription ─────────────────────────────────────────────────────────────
namespace App\Models;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'stripe_subscription_id', 'stripe_customer_id', 'stripe_price_id',
        'plan', 'status', 'trial_ends_at', 'current_period_start',
        'current_period_end', 'cancel_at_period_end',
    ];

    protected $casts = [
        'trial_ends_at'        => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end'   => 'datetime',
        'cancel_at_period_end' => 'boolean',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trialing']);
    }
}

// ─── Tag ─────────────────────────────────────────────────────────────────────
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'color'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function trades(): BelongsToMany
    {
        return $this->belongsToMany(Trade::class, 'tag_trade');
    }
}

// ─── Screenshot ───────────────────────────────────────────────────────────────
namespace App\Models;

class Screenshot extends Model
{
    use HasFactory;

    protected $fillable = ['trade_id', 'path', 'type', 'notes', 'size'];

    protected $appends = ['url'];

    public function trade(): BelongsTo { return $this->belongsTo(Trade::class); }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }
}

// ─── JournalEntry ────────────────────────────────────────────────────────────
namespace App\Models;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'date', 'mood', 'market_bias',
        'pre_session_notes', 'post_session_notes', 'lessons_learned',
    ];

    protected $casts = ['date' => 'date'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}

// ─── Goal ────────────────────────────────────────────────────────────────────
namespace App\Models;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'type', 'target_value', 'current_value',
        'period', 'start_date', 'end_date', 'is_completed',
    ];

    protected $casts = [
        'start_date'   => 'date',
        'end_date'     => 'date',
        'is_completed' => 'boolean',
        'target_value' => 'float',
        'current_value'=> 'float',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function getProgressPctAttribute(): float
    {
        if ($this->target_value == 0) return 0;
        return min(100, round(($this->current_value / $this->target_value) * 100, 1));
    }
}
