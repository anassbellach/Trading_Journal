<?php

namespace App\Http\Middleware;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props shared with every Inertia response.
     */
    public function share(Request $request): array
    {
        $user    = $request->user();
        $account = $user?->activeAccount();
        $accounts = $user?->accounts()->where('is_active', true)->get() ?? collect();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id'                 => $user->id,
                    'name'               => $user->name,
                    'email'              => $user->email,
                    'avatar'             => $user->avatar,
                    'subscription_plan'  => $user->subscription_plan,
                    'email_verified_at'  => $user->email_verified_at,
                ] : null,
            ],

            'activeAccount'   => $account ? [
                'id'               => $account->id,
                'name'             => $account->name,
                'broker'           => $account->broker,
                'type'             => $account->type,
                'currency'         => $account->currency,
                'starting_balance' => (float) $account->starting_balance,
                'current_balance'  => (float) $account->current_balance,
            ] : null,

            'accounts' => $accounts->map(fn ($a) => [
                'id'     => $a->id,
                'name'   => $a->name,
                'broker' => $a->broker,
                'type'   => $a->type,
            ]),

            'flash' => [
                'success' => $request->session()->get('success'),
                'error'   => $request->session()->get('error'),
                'info'    => $request->session()->get('info'),
            ],

            'unread_insights' => $user ? $user->unreadInsightsCount() : 0,

            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ]);
    }
}
