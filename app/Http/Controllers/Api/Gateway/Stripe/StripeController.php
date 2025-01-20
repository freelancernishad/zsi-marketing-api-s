<?php

namespace App\Http\Controllers\Api\Gateway\Stripe;

use Carbon\Carbon;
use Stripe\Stripe;
use Stripe\Webhook;
use App\Models\User;
use App\Models\Payment;
use Stripe\PaymentIntent;
use App\Models\UserPackage;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use App\Models\UserPackageAddon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class StripeController extends Controller
{
    // Set up Stripe API key
    public function __construct()
    {
        Stripe::setApiKey(config('STRIPE_SECRET'));
    }

    // Create a payment session for Stripe Checkout
    public function createCheckoutSession(Request $request)
    {
        // Get the authenticated user's ID
        $userId = auth()->id();

        // Validate incoming data
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|string|size:3',
            'success_url' => 'required|url',
            'cancel_url' => 'required|url',
            'coupon_id' => 'nullable|exists:coupons,id',
            'payable_type' => 'nullable|string',
            'payable_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Add authenticated user ID to the validated data
            $validatedData = $validator->validated();
            $validatedData['user_id'] = $userId;

            // Pass validated data to the helper function
            return createStripeCheckoutSession($validatedData);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function handleWebhook(Request $request)
    {
        // Set your Stripe webhook secret
        $endpointSecret = config('STRIPE_WEBHOOK_SECRET');

        // Get the payload and signature header
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            // Verify the webhook signature
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);

            // Handle the event
            switch ($event->type) {
                case 'checkout.session.completed':
                    // Handle successful checkout session (one-time or subscription)
                    $session = $event->data->object;

                    // Find the payment record (only for one-time payments)
                    if ($session->mode === 'payment') {
                        $payment = Payment::where('session_id', $session->id)->first();
                        if ($payment) {
                            // Update payment status
                            $payment->update([
                                'transaction_id' => $session->payment_intent,
                                'status' => 'completed',
                                'paid_at' => now(),
                                'response_data' => json_encode($event),
                            ]);

                            // If this is a package purchase, create or update the UserPackage
                            if ($payment->payable_type === 'App\\Models\\Package') {
                                $userPackage = UserPackage::updateOrCreate(
                                    ['id' => $payment->user_package_id], // Use the user_package_id if it exists
                                    [
                                        'user_id' => $payment->user_id,
                                        'package_id' => $payment->payable_id,
                                        'business_name' => $payment->business_name,
                                        'started_at' => now(),
                                        'ends_at' => now()->addMonths($payment->discount_months ?? 1), // Default to 1 month
                                        'stripe_subscription_id' => $session->subscription, // For recurring payments
                                        'stripe_customer_id' => $session->customer, // For recurring payments
                                        'status' => 'active',
                                    ]
                                );

                                // Update the payment with the UserPackage ID
                                $payment->update(['user_package_id' => $userPackage->id]);
                            }
                        }
                    }
                    break;

                    case 'invoice.payment_succeeded':
                        // Handle successful subscription payment
                        $invoice = $event->data->object;
                        Log::info($invoice);

                        // Find the UserPackage by Stripe subscription ID
                        $userPackage = UserPackage::where('stripe_subscription_id', $invoice->subscription)->first();
                        Log::info($userPackage);
                        // If UserPackage does not exist, create it
                        if (!$userPackage) {
                            // Retrieve the Stripe subscription to get details
                            $stripeSubscription = \Stripe\Subscription::retrieve($invoice->subscription);

                            // Retrieve the package ID from the subscription metadata or other source
                            $packageId = $stripeSubscription->metadata->package_id ?? null; // Adjust based on your metadata

                            // Retrieve the user ID from the Stripe customer
                            $stripeCustomer = \Stripe\Customer::retrieve($stripeSubscription->customer);
                            $user = User::where('stripe_customer_id', $stripeCustomer->id)->first();

                            if ($user && $packageId) {
                                // Create a new UserPackage
                                $userPackage = UserPackage::create([
                                    'user_id' => $user->id,
                                    'package_id' => $packageId,
                                    'business_name' => $stripeSubscription->metadata->business_name ?? null, // Adjust based on your metadata
                                    'started_at' => now(),
                                    'ends_at' => now()->addMonths(1), // Default to 1 month (adjust as needed)
                                    'stripe_subscription_id' => $invoice->subscription,
                                    'stripe_customer_id' => $stripeSubscription->customer,
                                    'status' => 'active',
                                ]);
                            } else {
                                // Log an error if the user or package ID is not found
                                Log::error("User or package not found for Stripe subscription: {$invoice->subscription}");
                                return response()->json(['error' => 'User or package not found'], 400);
                            }
                        }

                        // Create a new payment record for the successful charge
                        $payment = Payment::create([
                            'user_id' => $userPackage->user_id,
                            'gateway' => 'stripe',
                            'amount' => $invoice->amount_paid / 100, // Convert from cents to dollars
                            'currency' => $invoice->currency,
                            'status' => 'completed',
                            'transaction_id' => $invoice->payment_intent,
                            'paid_at' => now(),
                            'payable_type' => 'App\\Models\\Package',
                            'payable_id' => $userPackage->package_id,
                            'user_package_id' => $userPackage->id,
                            'business_name' => $userPackage->business_name,
                            'is_recurring' => true,
                            'response_data' => json_encode($event),
                        ]);

                        // Update the next billing date
                        $userPackage->update([
                            'next_billing_at' => Carbon::createFromTimestamp($invoice->lines->data[0]->period->end),
                        ]);

                        // Update UserPackageAddons with the payment ID
                        UserPackageAddon::where('user_id', $userPackage->user_id)
                            ->where('package_id', $userPackage->package_id)
                            ->update(['purchase_id' => $payment->id]);

                        break;

                case 'invoice.payment_failed':
                    // Handle failed subscription payment
                    $invoice = $event->data->object;

                    // Find the UserPackage by Stripe subscription ID
                    $userPackage = UserPackage::where('stripe_subscription_id', $invoice->subscription)->first();
                    if ($userPackage) {
                        // Create a new payment record for the failed charge
                        Payment::create([
                            'user_id' => $userPackage->user_id,
                            'gateway' => 'stripe',
                            'amount' => $invoice->amount_due / 100, // Convert from cents to dollars
                            'currency' => $invoice->currency,
                            'status' => 'failed',
                            'transaction_id' => $invoice->payment_intent,
                            'payable_type' => 'App\\Models\\Package',
                            'payable_id' => $userPackage->package_id,
                            'business_name' => $userPackage->business_name,
                            'is_recurring' => true,
                            'response_data' => json_encode($event),
                        ]);

                        // Notify the user about the failed payment (you can add this logic)
                        Log::warning("Payment failed for user {$userPackage->user_id} on subscription {$invoice->subscription}");
                    }
                    break;

                case 'customer.subscription.deleted':
                    // Handle subscription cancellation or expiration
                    $subscription = $event->data->object;

                    // Find the UserPackage by Stripe subscription ID
                    $userPackage = UserPackage::where('stripe_subscription_id', $subscription->id)->first();
                    if ($userPackage) {
                        // Mark the subscription as canceled
                        $userPackage->update([
                            'status' => 'canceled',
                            'canceled_at' => now(),
                        ]);
                    }
                    break;

                default:
                    // Log unhandled event types
                    Log::info('Unhandled Stripe event type: ' . $event->type);
                    break;
            }

            // Return a 200 response to Stripe
            return response()->json(['message' => 'Webhook handled'], 200);

        } catch (\Exception $e) {
            // Log any errors
            Log::error('Stripe webhook error: ' . $e->getMessage());

            // Return a 400 response to Stripe
            return response()->json(['error' => 'Webhook Error: ' . $e->getMessage()], 400);
        }
    }


    // Create a PaymentIntent (for processing payment)
    public function createPaymentIntent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|string|max:3',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        try {
            // Create PaymentIntent with Stripe
            $paymentIntent = PaymentIntent::create([
                'amount' => $validatedData['amount'] * 100, // Amount in cents
                'currency' => $validatedData['currency'],
                'payment_method_types' => ['card'],
            ]);

            // Respond with the client secret for the frontend to use
            return response()->json(['client_secret' => $paymentIntent->client_secret]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error creating PaymentIntent: ' . $e->getMessage()], 500);
        }
    }

    // Confirm the payment with a PaymentIntent
    public function confirmPaymentIntent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_intent_id' => 'required|string',
            'payment_method_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        try {
            // Confirm the payment with the provided payment method ID
            $paymentIntent = PaymentIntent::retrieve($validatedData['payment_intent_id']);
            $paymentIntent->confirm([
                'payment_method' => $validatedData['payment_method_id'],
            ]);

            // Respond with the payment status
            return response()->json(['status' => $paymentIntent->status]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error confirming PaymentIntent: ' . $e->getMessage()], 500);
        }
    }
}

