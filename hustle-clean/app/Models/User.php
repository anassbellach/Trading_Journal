<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'avatar', 'google_id',
        'subscription_plan', 'active_account_id', 'timezone',
    ];

    protected $hidden = ['password', 'remember_token', 'google_id'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }

    public function strategies(): HasMany
    {
        return $this->hasMany(Strategy::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    public function aiInsights(): HasMany
    {
        return $this->hasMany(AiInsight::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function activeAccount(): ?Account
    {
        if ($this->active_account_id) {
            return $this->accounts()->find($this->active_account_id);
        }
        return $this->accounts()->where('is_default', true)->first()
            ?? $this->accounts()->first();
    }

    public function isPro(): bool
    {
        return in_array($this->subscription_plan, ['pro', 'premium']);
    }

    public function isPremium(): bool
    {
        return $this->subscription_plan === 'premium';
    }

    public function unreadInsightsCount(): int
    {
        return $this->aiInsights()->where('is_read', false)->count();
    }
}
