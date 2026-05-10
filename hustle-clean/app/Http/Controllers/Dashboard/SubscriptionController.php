<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\StripeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function __construct(private readonly StripeService $stripe) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $sub  = $user->subscription;

        $plans = [
            [
                'id'          => 'free',
                'name'        => 'Free',
                'price'       => 0,
                'description' => 'Begin gratis',
                'features'    => ['Tot 50 trades/maand', '1 account', 'Basis analytics', 'Trade journal'],
                'price_id'    => null,
            ],
            [
                'id'          => 'pro',
                'name'        => 'Pro',
                'price'       => 29,
                'description' => 'Voor serieuze traders',
                'features'    => ['Onbeperkte trades', '5 accounts', 'Geavanceerde analytics', 'AI inzichten', 'CSV import/export', 'Dagboek'],
                'price_id'    => config('services.stripe.prices.pro'),
                'popular'     => true,
            ],
            [
                'id'          => 'premium',
                'name'        => 'Premium',
                'price'       => 59,
                'description' => 'Voor prop-firma traders',
                'features'    => ['Alles in Pro', 'Onbeperkte accounts', 'Prioritaire AI analyse', 'Doelen tracking', 'Vroege toegang', 'Prioritaire support'],
                'price_id'    => config('services.stripe.prices.premium'),
            ],
        ];

        return Inertia::render('Subscription/Index', [
            'plans'        => $plans,
            'subscription' => $sub,
            'currentPlan'  => $user->subscription_plan,
        ]);
    }

    public function checkout(Request $request): RedirectResponse
    {
        $request->validate(['price_id' => ['required', 'string']]);

        $session = $this->stripe->createCheckoutSession(
            $request->user(),
            $request->price_id
        );

        return redirect($session->url);
    }

    public function portal(Request $request): RedirectResponse
    {
        $session = $this->stripe->createBillingPortalSession($request->user());
        return redirect($session->url);
    }

    public function success(Request $request): Response
    {
        return Inertia::render('Subscription/Success');
    }
}

// ─── StripeWebhookController ──────────────────────────────────────────────────
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StripeWebhookController extends Controller
{
    public function __construct(private readonly StripeService $stripe) {}

    public function handle(Request $request): Response
    {
        $payload   = $request->getContent();
        $signature = $request->header('Stripe-Signature', '');

        try {
            $this->stripe->handleWebhook($payload, $signature);
            return response('OK', 200);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        } catch (\Exception $e) {
            \Log::error('Stripe webhook error: ' . $e->getMessage());
            return response('Webhook error', 500);
        }
    }
}
