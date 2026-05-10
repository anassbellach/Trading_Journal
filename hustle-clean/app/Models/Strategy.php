<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Strategy extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'description', 'color', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function user(): BelongsTo   { return $this->belongsTo(User::class); }
    public function trades(): HasMany   { return $this->hasMany(Trade::class); }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // Computed performance stats
    public function getWinRateAttribute(): float|null
    {
        $total = $this->trades()->where('status', 'closed')->count();
        if ($total === 0) return null;
        $wins = $this->trades()->where('status', 'closed')->where('is_win', true)->count();
        return round(($wins / $total) * 100, 1);
    }

    public function getTotalPnlAttribute(): float
    {
        return (float) $this->trades()->where('status', 'closed')->sum('pnl');
    }
}
