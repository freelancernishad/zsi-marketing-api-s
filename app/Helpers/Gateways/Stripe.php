<?php
use Stripe\Stripe;
use App\Models\Coupon;
use App\Models\Payment;
use Stripe\Checkout\Session;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;


if (!function_exists('createStripeCheckoutSession')) {
    /**
     * Create a Stripe Checkout Session
     *
     * @param array $data
     * @param string $successUrl
     * @param string $cancelUrl
     * @return JsonResponse
     */
    function createStripeCheckoutSession(array $data): JsonResponse
    {
        $amount = $data['amount'] ?? 100;
        $currency = $data['currency'] ?? 'USD';
        $userId = $data['user_id'] ?? null;
        $couponId = $data['coupon_id'] ?? null;
        $payableType = $data['payable_type'] ?? null;
        $payableId = $data['payable_id'] ?? null;

        $baseSuccessUrl = $data['success_url'] ?? 'http://localhost:8000/stripe/payment/success';
        $baseCancelUrl = $data['cancel_url'] ?? 'http://localhost:8000/stripe/payment/cancel';

        $discount = 0;

        if ($couponId) {
            $coupon = Coupon::find($couponId);
            if ($coupon && $coupon->isValid()) {
                $discount = $coupon->calculateDiscount($amount);
                $amount -= $discount;
            } else {
                return response()->json(['error' => 'Invalid or expired coupon'], 400);
            }
        }

        if ($amount <= 0) {
            return response()->json(['error' => 'Payment amount must be greater than zero'], 400);
        }

        $payment = Payment::create([
            'user_id' => $userId,
            'gateway' => 'stripe',
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'pending',
            'transaction_id' => uniqid(),
            'payable_type' => $payableType,
            'payable_id' => $payableId,
            'coupon_id' => $couponId,
        ]);

        try {
            Stripe::setApiKey(config('STRIPE_SECRET'));

            $successUrl = "{$baseSuccessUrl}?payment_id={$payment->id}&session_id={CHECKOUT_SESSION_ID}";
            $cancelUrl = "{$baseCancelUrl}?payment_id={$payment->id}&session_id={CHECKOUT_SESSION_ID}";

            $productName = 'Payment';
            if ($payableType && $payableId) {
                $payable = app($payableType)->find($payableId);
                $productName = $payable ? $payable->name : $productName;
            }






            $session = Session::create([
                'payment_method_types' => ['card', 'amazon_pay', 'us_bank_account'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => $currency,
                            'product_data' => ['name' => $productName],
                            'unit_amount' => $amount * 100,
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ]);

            $payment->update(['transaction_id' => $session->id]);

            return response()->json(['session_url' => $session->url]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
