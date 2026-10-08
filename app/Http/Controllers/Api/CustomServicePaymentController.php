<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomServicePayment;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class CustomServicePaymentController extends Controller
{
    /**
     * Display a listing of custom service payments.
     */
    public function index(Request $request)
    {
        $query = CustomServicePayment::query();

        if ($request->has('email')) {
            $query->where('customer_email', $request->email);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    /**
     * Store a newly created custom service payment and generate Stripe checkout URL from backend.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_type' => 'nullable|string',
            'service_name' => 'required|string',
            'payment_type' => 'required|in:one_time,recurring',
            'billing_cycle' => 'nullable|string',
            'amount' => 'required|numeric|min:1',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'payment_method' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Fetch active STRIPE_SECRET directly from system_settings DB table
        $stripeSecret = SystemSetting::where('key', 'STRIPE_SECRET')->value('value')
            ?? config('STRIPE_SECRET')
            ?? env('STRIPE_SECRET');

        if (!$stripeSecret) {
            return response()->json([
                'success' => false,
                'message' => 'Stripe secret key is not configured in backend system_settings database table'
            ], 500);
        }

        Stripe::setApiKey($stripeSecret);

        $origin = $request->header('origin') ?? $request->header('referer') ?? 'http://localhost:3000';
        $origin = rtrim($origin, '/');
        $amountInCents = (int) round($validated['amount'] * 100);

        try {
            $isRecurring = ($validated['payment_type'] === 'recurring');

            $lineItem = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $validated['service_name'] . ($isRecurring ? ' (Monthly Subscription)' : ''),
                    ],
                    'unit_amount' => $amountInCents,
                ],
                'quantity' => 1,
            ];

            if ($isRecurring) {
                $lineItem['price_data']['recurring'] = ['interval' => 'month'];
            }

            $sessionData = [
                'payment_method_types' => ['card'],
                'mode' => $isRecurring ? 'subscription' : 'payment',
                'customer_email' => $validated['customer_email'],
                'line_items' => [$lineItem],
                'success_url' => "{$origin}/custom-payment?session_id={CHECKOUT_SESSION_ID}&success=true",
                'cancel_url' => "{$origin}/custom-payment?canceled=true",
                'metadata' => [
                    'customer_name' => $validated['customer_name'],
                    'customer_phone' => $validated['customer_phone'] ?? '',
                    'service_name' => $validated['service_name'],
                    'notes' => $validated['notes'] ?? '',
                ],
            ];

            $session = Session::create($sessionData);

            $validated['transaction_id'] = $session->id;
            $validated['status'] = 'pending';
            $validated['payment_method'] = 'stripe';

            $payment = CustomServicePayment::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Stripe Checkout session generated from backend successfully',
                'url' => $session->url,
                'checkout_url' => $session->url,
                'session_id' => $session->id,
                'data' => $payment
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Stripe Session Generation Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate Stripe checkout URL from backend: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified custom service payment.
     */
    public function show($id)
    {
        $payment = CustomServicePayment::find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment record not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }
}
