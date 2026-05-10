<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\User;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\BillingPortal\Session as BillingSession;
use Stripe\Webhook;
use Stripe\Event;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Get or create a Stripe customer for the user.
     */
    public function getOrCreateCustomer(User $user): Customer
    {
        $sub = $user->subscription;

        if ($sub?->stripe_customer_id) {
            return Customer::retrieve($sub->stripe_customer_id);
        }

        $customer = Customer::create([
            'email' => $user->email,
            'name'  => $user->name,
            'metadata' => ['user_id' => $user->id],
        ]);

        Subscription::updateOrCreate(
            ['user_id' => $user->id],
            ['stripe_customer_id' => $customer->id]
        );

        return $customer;
    }

    /**
     * Create a Stripe Checkout session for a plan.
     */
    public function createCheckoutSession(User $user, string $priceId): CheckoutSession
    {
        $customer = $this->getOrCreateCustomer($user);

        return CheckoutSession::create([
            'customer'   => $customer->id,
            'mode'       => 'subscription',
            'line_items' => [['price' => $priceId, 'quantity' => 1]],
            'success_url'=> route('subscription.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('subscription.index'),
            'metadata'   => ['user_id' => $user->id],
            'subscription_data' => [
                'trial_period_days' => 14,
                'metadata' => ['user_id' => $user->id],
            ],
        ]);
    }

    /**
     * Create a billing portal session.
     */
    public function createBillingPortalSession(User $user): BillingSession
    {
        $customer = $this->getOrCreateCustomer($user);

        return BillingSession::create([
            'customer'   => $customer->id,
            'return_url' => route('subscription.index'),
        ]);
    }

    /**
     * Handle incoming Stripe webhook.
     */
    public function handleWebhook(string $payload, string $signature): void
    {
        $event = Webhook::constructEvent(
            $payload,
            $signature,
            config('services.stripe.webhook_secret')
        );

        match ($event->type) {
            'customer.subscription.created',
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($event),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event),
            'invoice.payment_failed'        => $this->handlePaymentFailed($event),
            default                         => null,
        };
    }

    private function handleSubscriptionUpdated(Event $event): void
    {
        $stripeSub = $event->data->object;
        $userId    = $stripeSub->metadata->user_id ?? null;

        if (! $userId) return;

        $plan = $this->resolvePlan($stripeSub->items->data[0]->price->id ?? '');

        Subscription::updateOrCreate(
            ['user_id' => $userId],
            [
                'stripe_subscription_id' => $stripeSub->id,
                'stripe_customer_id'     => $stripeSub->customer,
                'stripe_price_id'        => $stripeSub->items->data[0]->price->id ?? null,
                'plan'                   => $plan,
                'status'                 => $stripeSub->status,
                'trial_ends_at'          => $stripeSub->trial_end ? now()->setTimestamp($stripeSub->trial_end) : null,
                'current_period_start'   => now()->setTimestamp($stripeSub->current_period_start),
                'current_period_end'     => now()->setTimestamp($stripeSub->current_period_end),
                'cancel_at_period_end'   => $stripeSub->cancel_at_period_end,
            ]
        );

        \App\Models\User::where('id', $userId)->update(['subscription_plan' => $plan]);
    }

    private function handleSubscriptionDeleted(Event $event): void
    {
        $stripeSub = $event->data->object;
        $userId    = $stripeSub->metadata->user_id ?? null;

        if (! $userId) return;

        Subscription::where('stripe_subscription_id', $stripeSub->id)->update([
            'status' => 'canceled',
            'cancel_at_period_end' => true,
        ]);

        \App\Models\User::where('id', $userId)->update(['subscription_plan' => 'free']);
    }

    private function handlePaymentFailed(Event $event): void
    {
        $invoice = $event->data->object;
        $userId  = $invoice->subscription_details?->metadata?->user_id ?? null;

        if ($userId) {
            Subscription::where('user_id', $userId)->update(['status' => 'past_due']);
        }
    }

    private function resolvePlan(string $priceId): string
    {
        $map = [
            config('services.stripe.prices.pro')     => 'pro',
            config('services.stripe.prices.premium') => 'premium',
        ];

        return $map[$priceId] ?? 'free';
    }
}
