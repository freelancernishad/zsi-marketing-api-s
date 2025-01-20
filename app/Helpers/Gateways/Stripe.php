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
use Illuminate\Support\Facades\Validator;

function createStripeCheckoutSession(array $data): JsonResponse
{
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

    if ($finalAmount <= 0) {
        return response()->json(['error' => 'Payment amount must be greater than zero'], 400);
    }

    // Create the payment record
    $payment = Payment::create([
        'user_id' => $userId,
        'gateway' => 'stripe',
        'amount' => $finalAmount,
        'currency' => $currency,
        'status' => 'pending',
        'transaction_id' => uniqid(),
        'payable_type' => $payableType,
        'payable_id' => $payableId,
        'business_name' => $business_name,
        'coupon_id' => $couponId,
        'is_recurring' => $isRecurring,
    ]);

    try {
        Stripe::setApiKey(config('STRIPE_SECRET'));

        // Create or retrieve Stripe Customer
        $user = User::find($userId);
        if (!$user->stripe_customer_id) {
            $customer = \Stripe\Customer::create([
                'email' => $user->email,
                'name' => $user->name,
            ]);
            $user->stripe_customer_id = $customer->id;
            $user->save();
        }

        // Success and Cancel URLs
        $successUrl = "{$baseSuccessUrl}?payment_id={$payment->id}&session_id={CHECKOUT_SESSION_ID}";
        $cancelUrl = "{$baseCancelUrl}?payment_id={$payment->id}&session_id={CHECKOUT_SESSION_ID}";

        // Create Stripe Subscription for recurring payments
        if ($isRecurring) {
            // Create Stripe Price for the base package
            $stripePrice = \Stripe\Price::create([
                'unit_amount' => $finalAmount * 100,
                'currency' => $currency,
                'recurring' => ['interval' => 'month'], // Adjust interval as needed
                'product_data' => [
                    'name' => 'Subscription for ' . $payableType,
                ],
            ]);

            // Prepare line items for the subscription
            $lineItems = [
                [
                    'price' => $stripePrice->id,
                    'quantity' => 1,
                ],
            ];

            // Add addons as additional line items
            $addonTotal = 0;
            if (!empty($addonIds)) {
                foreach ($addonIds as $addonId) {
                    $addon = PackageAddon::find($addonId);
                    if ($addon) {
                        // Create Stripe Price for the addon
                        $addonPrice = \Stripe\Price::create([
                            'unit_amount' => $addon->price * 100,
                            'currency' => $currency,
                            'recurring' => ['interval' => 'month'], // Same interval as the base package
                            'product_data' => [
                                'name' => $addon->addon_name,
                            ],
                        ]);

                        // Add the addon to the line items
                        $lineItems[] = [
                            'price' => $addonPrice->id,
                            'quantity' => 1,
                        ];

                        $addonTotal += $addon->price;
                    }
                }

                // Add the addon total to the final payment amount
                $finalAmount += $addonTotal;

                // Create user package addons
                createUserPackageAddons($userId, $payableId, $addonIds, $payment->id);
            }

            // Create the Stripe subscription with all line items
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card', 'amazon_pay', 'us_bank_account'],
                'mode' => 'subscription',
                'customer' => $user->stripe_customer_id,
                'line_items' => $lineItems,
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ]);
        } else {
            // One-time payment
            $lineItems = [
                [
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => 'One-time Payment for ' . $payableType,
                        ],
                        'unit_amount' => $finalAmount * 100,
                    ],
                    'quantity' => 1,
                ],
            ];

            // Add addons as additional line items for one-time payment
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
                                'unit_amount' => $addon->price * 100,
                            ],
                            'quantity' => 1,
                        ];

                        $addonTotal += $addon->price;
                    }
                }

                // Add the addon total to the final payment amount
                $finalAmount += $addonTotal;

                // Create user package addons
                createUserPackageAddons($userId, $payableId, $addonIds, $payment->id);
            }

            // Create the Stripe Checkout session for one-time payment
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card', 'amazon_pay', 'us_bank_account'],
                'mode' => 'payment',
                'customer' => $user->stripe_customer_id,
                'line_items' => $lineItems,
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ]);
        }

        // Update payment with session ID
        $payment->update(['session_id' => $session->id]);

        return response()->json(['session_url' => $session->url]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}



/**
 * Create user_package_addons for a user based on selected addons.
 *
 * @param int $userId
 * @param int $packageId
 * @param array $addonIds
 * @param int $purchaseId
 * @return void
 */
function createUserPackageAddons(int $userId, int $packageId, array $addonIds, int $purchaseId): void
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
