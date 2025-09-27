<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PricingPlan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class PaymentController extends Controller
{
    private $stripe;

    public function __construct()
    {
        $stripeKey = config('services.stripe.secret_key');
        if ($stripeKey) {
            $this->stripe = new StripeClient($stripeKey);
        }
    }

    /**
     * Show pricing plans.
     */
    public function index()
    {
        $plans = PricingPlan::active()->orderBy('price')->get();
        $user = Auth::user();
        
        return view('payment.index', compact('plans', 'user'));
    }

    /**
     * Create payment intent for a plan.
     */
    public function createPaymentIntent(Request $request)
    {
        if (!$this->stripe) {
            return response()->json([
                'success' => false,
                'message' => 'Payment system not configured. Please contact administrator.'
            ], 500);
        }

        $request->validate([
            'plan_id' => 'required|exists:pricing_plans,id'
        ]);

        $user = Auth::user();
        $plan = PricingPlan::findOrFail($request->plan_id);

        // Check if user can take test
        if ($user->canTakeTest()) {
            return response()->json([
                'success' => false,
                'message' => 'You already have access to take tests. No payment required.',
                'redirect' => route('test.show')
            ]);
        }

        try {
            // Create or retrieve Stripe customer
            $customer = $this->getOrCreateStripeCustomer($user);

            // Apply user's discount
            $discountPercentage = $user->getEffectiveDiscountPercentage();
            $finalPrice = $plan->getPriceWithDiscount($discountPercentage);

            // Create payment intent
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => (int)($finalPrice * 100), // Convert to cents
                'currency' => $plan->currency,
                'customer' => $customer->id,
                'metadata' => [
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->name,
                    'original_price' => $plan->price,
                    'discount_percentage' => $discountPercentage,
                    'final_price' => $finalPrice,
                ],
                'description' => "Payment for {$plan->name} - English Speaking Test",
            ]);

            // Store payment record
            $payment = Payment::create([
                'user_id' => $user->id,
                'stripe_payment_intent_id' => $paymentIntent->id,
                'stripe_customer_id' => $customer->id,
                'amount' => $finalPrice,
                'currency' => $plan->currency,
                'status' => 'pending',
                'description' => "Payment for {$plan->name}",
                'metadata' => [
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->name,
                    'discount_percentage' => $discountPercentage,
                ],
            ]);

            return response()->json([
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_id' => $payment->id,
                'amount' => $finalPrice,
                'currency' => $plan->currency,
                'plan_name' => $plan->name,
                'discount_applied' => $discountPercentage > 0,
                'discount_percentage' => $discountPercentage,
            ]);

        } catch (ApiErrorException $e) {
            Log::error('Stripe API Error', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'plan_id' => $plan->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment processing error. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::error('Payment creation error', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'plan_id' => $plan->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }

    /**
     * Handle successful payment.
     */
    public function handleSuccessfulPayment(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
            'payment_id' => 'required|exists:payments,id'
        ]);

        $user = Auth::user();
        $payment = Payment::where('id', $request->payment_id)
                         ->where('user_id', $user->id)
                         ->firstOrFail();

        try {
            // Retrieve payment intent from Stripe
            $paymentIntent = $this->stripe->paymentIntents->retrieve($request->payment_intent_id);

            if ($paymentIntent->status === 'succeeded') {
                // Update payment status
                $payment->update([
                    'status' => 'succeeded',
                    'paid_at' => now(),
                    'payment_method' => $paymentIntent->charges->data[0]->payment_method_details->type ?? 'unknown',
                ]);

                // Create subscription
                $plan = PricingPlan::findOrFail($payment->metadata['plan_id']);
                $subscription = Subscription::create([
                    'user_id' => $user->id,
                    'pricing_plan_id' => $plan->id,
                    'stripe_customer_id' => $payment->stripe_customer_id,
                    'status' => 'active',
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'test_limit' => $plan->test_limit,
                    'tests_used' => 0,
                    'starts_at' => now(),
                    'ends_at' => now()->addDays($plan->validity_days),
                    'metadata' => [
                        'payment_id' => $payment->id,
                        'stripe_payment_intent_id' => $paymentIntent->id,
                    ],
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment successful! You can now take your test.',
                    'subscription' => $subscription,
                    'redirect' => route('test.show')
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not completed. Please try again.',
                ], 400);
            }

        } catch (ApiErrorException $e) {
            Log::error('Stripe payment verification error', [
                'error' => $e->getMessage(),
                'payment_intent_id' => $request->payment_intent_id,
                'payment_id' => $payment->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed. Please contact support.',
            ], 500);
        }
    }

    /**
     * Get or create Stripe customer.
     */
    private function getOrCreateStripeCustomer($user)
    {
        if ($user->stripe_customer_id) {
            try {
                return $this->stripe->customers->retrieve($user->stripe_customer_id);
            } catch (ApiErrorException $e) {
                // Customer not found, create new one
            }
        }

        $customer = $this->stripe->customers->create([
            'email' => $user->email,
            'name' => $user->name,
            'metadata' => [
                'user_id' => $user->id,
            ],
        ]);

        $user->update(['stripe_customer_id' => $customer->id]);

        return $customer;
    }

    /**
     * Show payment history.
     */
    public function history()
    {
        $user = Auth::user();
        $payments = $user->payments()->with('subscription')->latest()->paginate(10);
        
        return view('payment.history', compact('payments'));
    }

    /**
     * Webhook handler for Stripe events.
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\Exception $e) {
            Log::error('Stripe webhook signature verification failed', [
                'error' => $e->getMessage()
            ]);
            return response('Webhook signature verification failed', 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;
            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event->data->object);
                break;
            default:
                Log::info('Unhandled Stripe event type: ' . $event->type);
        }

        return response('OK', 200);
    }

    /**
     * Handle successful payment intent.
     */
    private function handlePaymentIntentSucceeded($paymentIntent)
    {
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();
        
        if ($payment && $payment->status === 'pending') {
            $payment->update([
                'status' => 'succeeded',
                'paid_at' => now(),
            ]);
        }
    }

    /**
     * Handle failed payment intent.
     */
    private function handlePaymentIntentFailed($paymentIntent)
    {
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();
        
        if ($payment && $payment->status === 'pending') {
            $payment->update([
                'status' => 'failed',
            ]);
        }
    }
}