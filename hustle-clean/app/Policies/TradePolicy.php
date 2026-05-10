<?php

namespace App\Policies;

use App\Models\Trade;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TradePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Trade $trade): bool
    {
        return $user->id === $trade->user_id;
    }

    public function create(User $user): bool
    {
        // Free plan: max 50 closed trades
        if ($user->subscription_plan === 'free') {
            $count = Trade::where('user_id', $user->id)->where('status', 'closed')->count();
            return $count < 50;
        }
        return true;
    }

    public function update(User $user, Trade $trade): bool
    {
        return $user->id === $trade->user_id;
    }

    public function delete(User $user, Trade $trade): bool
    {
        return $user->id === $trade->user_id;
    }

    public function restore(User $user, Trade $trade): bool
    {
        return $user->id === $trade->user_id;
    }

    public function forceDelete(User $user, Trade $trade): bool
    {
        return $user->id === $trade->user_id;
    }
}
