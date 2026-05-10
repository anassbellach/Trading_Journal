<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// ─── Account ──────────────────────────────────────────────────────────────────
class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'broker', 'type', 'currency',
        'starting_balance', 'current_balance',
        'max_daily_loss', 'max_daily_loss_pct', 'is_default', 'is_active',
    ];

    protected $casts = [
        'starting_balance'  => 'decimal:2',
        'current_balance'   => 'decimal:2',
        'max_daily_loss'    => 'decimal:2',
        'max_daily_loss_pct'=> 'decimal:2',
        'is_default'        => 'boolean',
        'is_active'         => 'boolean',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function trades(): HasMany  { return $this->hasMany(Trade::class); }
}
