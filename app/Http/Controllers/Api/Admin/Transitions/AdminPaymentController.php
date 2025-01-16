<?php

namespace App\Http\Controllers\Api\Admin\Transitions;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AdminPaymentController extends Controller
{
    /**
     * Get all types of transaction history.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllTransactionHistory(Request $request)
    {
        // Initialize query
        $query = Payment::query();

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        } else {
            $query->where('status', 'completed');
        }

        // Filter by gateway if provided
        if ($request->has('gateway')) {
            $query->where('gateway', $request->input('gateway'));
        }

        // Filter by specific payable type and ID
        if ($request->has('payable_type') && $request->has('payable_id')) {
            $query->where('payable_type', $request->input('payable_type'))
                ->where('payable_id', $request->input('payable_id'));
        }

        // Filter by coupon usage
        if ($request->has('coupon_id')) {
            $query->where('coupon_id', $request->input('coupon_id'));
        }

        // Handle user_id based on the guard
        if (Auth::guard('user')->check()) {
            // If the guard is 'user', get the user_id from the authenticated user
            $query->where('user_id', Auth::guard('user')->id());
        } elseif ($request->has('user_id')) {
            // For other guards, get the user_id from the request
            $query->where('user_id', $request->input('user_id'));
        }

        // Apply date range filter based on `created_at`
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [
                $request->input('start_date'),
                $request->input('end_date'),
            ]);
        }

        // Apply date range filter based on `paid_at`
        if ($request->has('paid_start_date') && $request->has('paid_end_date')) {
            $query->whereBetween('paid_at', [
                $request->input('paid_start_date'),
                $request->input('paid_end_date'),
            ]);
        }


        // Search by transaction_id if provided
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where('transaction_id', 'like', '%' . $searchTerm . '%');
        }


        // Select specific fields and include user data
        $query->with(['user' => function ($query) {
            $query->select([
                'id',
                'profile_picture',
                'name',
                'email',
            ]);
        }]);

        // Include payable (ServicePurchased or Package) with necessary fields
        $query->with(['payable']);

        // Fetch results with pagination and order by `paid_at` descending
        $transactions = $query->orderBy('paid_at', 'desc')->paginate($request->input('per_page', 15));

        // Transform the response to include user data, due_amount, service_details, and package name
        $transactions->getCollection()->transform(function ($payment) {
            $servicePurchased = $payment->payable;

        // Initialize service_details and package_name
        $serviceDetails =  null;
        $packageName = null;

        // Check if payable is a package and include package name in service_details
        if ($payment->payable_type === 'App\\Models\\Package') {
            $packageName = $servicePurchased->name ?? " "; // Get the package name

            // If service_details is not already an array, initialize it
            if (!is_array($serviceDetails)) {
                $serviceDetails = [
                    'selected_services' => []
                ];
            }
            // Add the package name to the selected_services array
            $serviceDetails['selected_services'][] = $packageName;
        }

        return [
            'id' => $payment->id,
            'transaction_id' => $payment->transaction_id,
            'name' => $payment->user->name,
            'email' => $payment->user->email,
            'profile_picture' => $payment->user->profile_picture,
            'amount' => $payment->amount,
            'paid_at' => $payment->paid_at,
            'status' => $payment->status,
            'service_details' => $serviceDetails, // Updated service_details with package name

        ];
        });

        return response()->json($transactions);
    }


    public function getTransactionById(Request $request, $id)
    {
        // Find the payment by ID
        $payment = Payment::with(['user', 'payable'])
            ->find($id);

        // Check if the payment exists
        if (!$payment) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        // Transform the response
        $servicePurchased = $payment->payable;
        // Check if payment_method_details is a string and decode it if necessary
        $paymentMethodDetails = $payment->payment_method_details;
        if (is_string($paymentMethodDetails)) {
            $paymentMethodDetails = json_decode($paymentMethodDetails);
        }

        $response = [
            'id' => $payment->id,
            'transaction_id' => $payment->transaction_id,
            'client_id' => $payment->user->client_id,
            'name' => $payment->user->name,
            'email' => $payment->user->email,
            'profile_picture' => $payment->user->profile_picture,
            'amount' => $payment->amount,
            'paid_at' => $payment->paid_at,
            'event' => $payment->event,
            'status' => $payment->status,
            'payment_method_details' => $paymentMethodDetails,
            'due_amount' => $servicePurchased ? $servicePurchased->due_amount : 0, // Add due_amount at root level
            'service_details' => $servicePurchased ? $servicePurchased->formatted_service_details : 0, // Add service_details at root level
        ];

        return response()->json($response);
    }

}
