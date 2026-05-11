<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function __construct(private readonly StripeService $stripe) {}

    public function handle(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature', '');

        try {
            $this->stripe->handleWebhook($payload, $signature);

            return response('OK', 200);
        } catch (SignatureVerificationException) {
            return response('Invalid signature', 400);
        } catch (\Throwable $exception) {
            Log::error('Stripe webhook error: '.$exception->getMessage(), [
                'exception' => $exception,
            ]);

            return response('Webhook error', 500);
        }
    }
}
