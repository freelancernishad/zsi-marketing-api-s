<?php

use Stripe\Stripe;
use App\Models\User;
use App\Models\Coupon;
use App\Models\Package;
use App\Models\Payment;
use App\Models\PackageAddon;
use Stripe\Checkout\Session;
use App\Models\UserPackageAddon;
use Illuminate\Http\JsonResponse;
use Stripe\TestHelpers\TestClock;

function createStripeCheckoutSession(array $data): JsonResponse
{
    // Default values for optional parameters
    $amount = $data['amount'] ?? 100;
    $currency = $data['currency'] ?? 'USD';
    $userId = $data['user_id'] ?? null;
    $couponId = $data['coupon_id'] ?? null;
    $payableType = $data['payable_type'] ?? null;
    $payableId = $data['payable_id'] ?? null;
    $business_name = $data['business_name'] ?? null;
    $addonIds = $data['addon_ids'] ?? [];
    $isRecurring = $data['is_recurring'] ?? false;
    $baseSuccessUrl = $data['success_url'] ?? 'http://localhost:8000/stripe/payment/success';
    $baseCancelUrl = $data['cancel_url'] ?? 'http://localhost:8000/stripe/payment/cancel';

    // Initialize discount and final amount
    $discount = 0;
    $finalAmount = $amount;

    // Handle coupon discount
    if ($couponId) {
        $coupon = Coupon::find($couponId);
        if ($coupon && $coupon->isValid()) {
            $discount = $coupon->getDiscountAmount($amount);
            $finalAmount -= $discount;
        } else {
            return response()->json(['error' => 'Invalid or expired coupon'], 400);
        }
    }

    // Ensure the final amount is greater than zero
    if ($finalAmount <= 0) {
        return response()->json(['error' => 'Payment amount must be greater than zero'], 400);
    }

    try {
        // Set Stripe API key
        Stripe::setApiKey(config('STRIPE_SECRET'));

        // Retrieve or create Stripe Customer
        $user = User::find($userId);
        if (!$user->stripe_customer_id) {
            $customer = \Stripe\Customer::create([
                'email' => $user->email,
                'name' => $user->name,
            ]);
            $user->stripe_customer_id = $customer->id;
            $user->save();
        } else {
            try {
                // Verify if the existing Stripe customer ID is valid
                \Stripe\Customer::retrieve($user->stripe_customer_id);
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                // If the customer ID is invalid, create a new customer
                if ($e->getHttpStatus() === 404) { // 404 means "Not Found"
                    $customer = \Stripe\Customer::create([
                        'email' => $user->email,
                        'name' => $user->name,
                    ]);
                    $user->stripe_customer_id = $customer->id;
                    $user->save();
                } else {
                    // Re-throw the exception if it's not a "Not Found" error
                    throw $e;
                }
            }
        }

        // Prepare success and cancel URLs
        $successUrl = "{$baseSuccessUrl}?session_id={CHECKOUT_SESSION_ID}";
        $cancelUrl = "{$baseCancelUrl}?session_id={CHECKOUT_SESSION_ID}";

        // Prepare line items for the Checkout Session
        $lineItems = [];

        // Add base package price to line items
        if ($payableType === 'App\\Models\\Package' && $payableId) {
            $payable = Package::find($payableId);
            if ($payable) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => $payable->name,
                        ],
                        'unit_amount' => $finalAmount * 100, // Amount in cents
                        'recurring' => $isRecurring ? ['interval' => 'day'] : null,
                    ],
                    'quantity' => 1,
                ];
            }
        }

        // Add addons as additional line items
        $addonTotal = 0;
        if (!empty($addonIds)) {
            foreach ($addonIds as $addonId) {
                $addon = PackageAddon::find($addonId);
                if ($addon) {
                    $lineItems[] = [
                        'price_data' => [
                            'currency' => $currency,
                            'product_data' => [
                                'name' => $addon->addon_name,
                            ],
                            'unit_amount' => $addon->price * 100, // Addon price in cents
                            'recurring' => $isRecurring ? ['interval' => 'day'] : null,
                        ],
                        'quantity' => 1,
                    ];
                    $addonTotal += $addon->price;
                }
            }

            // Add the addon total to the final payment amount
            $finalAmount += $addonTotal;

            // Create user package addons
            createUserPackageAddons($userId, $payableId, $addonIds, null); // Pass null for purchase_id (will be updated later)
        }

        // Step 1: Create a test clock (only in test mode)
        $testClock = TestClock::create([
            'frozen_time' => time() + 300, // Freeze time 5 minutes from now
        ]);

        // Step 2: Create the subscription directly (not using Checkout Session)
        $subscriptionData = [
            'customer' => $user->stripe_customer_id,
            'items' => [
                [
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => $payable->name,
                        ],
                        'unit_amount' => $finalAmount * 100, // Amount in cents
                        'recurring' => [
                            'interval' => 'day', // Set interval to daily
                        ],
                    ],
                    'quantity' => 1,
                ],
            ],
            'metadata' => [
                'package_id' => $payableId, // Include package ID in metadata
                'business_name' => $business_name, // Include business name in metadata
            ],
        ];

        // Create the subscription
        $subscription = \Stripe\Subscription::create($subscriptionData);

        // Step 3: Advance the test clock by 5 minutes
        $testClock->advance([
            'frozen_time' => time() + 300, // Advance by 5 minutes (300 seconds)
        ]);

        // Step 4: Create a Checkout Session for the subscription (optional)
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card', 'amazon_pay', 'us_bank_account'],
            'mode' => 'subscription',
            'customer' => $user->stripe_customer_id,
            'line_items' => [
                [
                    'price' => $subscription->items->data[0]->price->id, // Use the price ID from the subscription
                    'quantity' => 1,
                ],
            ],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);

        // Create a payment record only for one-time payments
        if (!$isRecurring) {
            $payment = Payment::create([
                'user_id' => $userId,
                'gateway' => 'stripe',
                'amount' => $finalAmount,
                'currency' => $currency,
                'status' => 'pending',
                'transaction_id' => $session->id, // Use session ID as transaction ID
                'payable_type' => $payableType,
                'payable_id' => $payableId,
                'business_name' => $business_name,
                'coupon_id' => $couponId,
                'is_recurring' => false,
            ]);

            // Update the session URL with the payment ID
            $successUrl = "{$baseSuccessUrl}?payment_id={$payment->id}&session_id={CHECKOUT_SESSION_ID}";
            $cancelUrl = "{$baseCancelUrl}?payment_id={$payment->id}&session_id={CHECKOUT_SESSION_ID}";

            // Update the session with the new URLs
            $session = \Stripe\Checkout\Session::update($session->id, [
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ]);
        }

        // Return the Checkout Session URL
        return response()->json(['session_url' => $session->url]);
    } catch (\Exception $e) {
        // Handle any exceptions
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

/**
 * Create user_package_addons for a user based on selected addons.
 *
 * @param int $userId
 * @param int $packageId
 * @param array $addonIds
 * @param int|null $purchaseId
 * @return void
 */
function createUserPackageAddons(int $userId, int $packageId, array $addonIds, $purchaseId): void
{
    foreach ($addonIds as $addonId) {
        UserPackageAddon::create([
            'user_id' => $userId,
            'package_id' => $packageId,
            'addon_id' => $addonId,
            'purchase_id' => $purchaseId,
        ]);
    }
}
