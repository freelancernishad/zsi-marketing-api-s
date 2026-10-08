<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomServicePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
     * Store a newly created custom service payment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_type' => 'required|string',
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

        $validated['transaction_id'] = 'TXN-' . strtoupper(Str::random(10));
        $validated['status'] = 'completed'; // auto mark completed for demo / processing

        $payment = CustomServicePayment::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Payment request submitted successfully',
            'data' => $payment
        ], 201);
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
