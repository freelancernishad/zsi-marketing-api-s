<?php

namespace App\Http\Controllers\Api\Coupon;

use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Http\Request;
use App\Models\CouponAssociation;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    // Store a new coupon
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|unique:coupons,code',
            'type' => 'required|string|in:percentage,flat',
            'value' => 'required|numeric|min:0',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'usage_limit' => 'nullable|integer|min:0',
            'is_active' => 'required|boolean',
            'associations' => 'nullable|array',
            'associations.*.item_id' => 'required_with:associations|integer',
            'associations.*.item_type' => 'required_with:associations|in:user,package,service',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validated = $request->all();
        // Create the coupon
        $coupon = Coupon::create($validated);

        // Handle associations if provided
        if ($request->has('associations') && !empty($request->associations)) {
            foreach ($request->associations as $association) {
                CouponAssociation::create([
                    'coupon_id' => $coupon->id,
                    'item_id' => $association['item_id'],
                    'item_type' => $association['item_type'],
                ]);
            }
        }

        return response()->json([
            'message' => 'Coupon created successfully',
            'coupon' => $coupon
        ], 201);
    }

    // Apply coupon to a user's order
    public function apply(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required|string|exists:coupons,code',
            'order_total' => 'required|numeric|min:0',
            'user_id' => 'nullable|exists:users,id', // Optional if the coupon applies to a specific user
            'package_id' => 'nullable|exists:packages,id', // Optional if the coupon applies to a specific package
            'service_id' => 'nullable|exists:services,id', // Optional if the coupon applies to a specific service
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $validated = $request->all();


        $coupon = Coupon::where('code', $validated['coupon_code'])->first();

        if (!$coupon || !$coupon->is_active) {
            return response()->json(['message' => 'Coupon is invalid or expired'], 400);
        }

        // Check if the coupon is within the valid date range
        $now = now();
        if ($coupon->valid_from > $now || $coupon->valid_until < $now) {
            return response()->json(['message' => 'Coupon is not valid for the current date'], 400);
        }

        // Check if the coupon is associated with the provided user, package, or service
        $validAssociation = CouponAssociation::where('coupon_id', $coupon->id)
            ->where(function ($query) use ($validated) {
                if (isset($validated['user_id'])) {
                    $query->where('item_id', $validated['user_id'])->where('item_type', 'user');
                }
                if (isset($validated['package_id'])) {
                    $query->where('item_id', $validated['package_id'])->where('item_type', 'package');
                }
                if (isset($validated['service_id'])) {
                    $query->where('item_id', $validated['service_id'])->where('item_type', 'service');
                }
            })
            ->exists();

        if (!$validAssociation) {
            return response()->json(['message' => 'Coupon is not valid for the provided item'], 400);
        }

        // Calculate the discount based on coupon type
        $discount = 0;
        if ($coupon->type === 'percentage') {
            $discount = ($validated['order_total'] * $coupon->value) / 100;
        } elseif ($coupon->type === 'flat') {
            $discount = $coupon->value;
        }

        // Apply discount and return the updated order total
        $discounted_total = $validated['order_total'] - $discount;

        // Track coupon usage
        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'order_total' => $validated['order_total'],
            'discount' => $discount,
        ]);

        return response()->json([
            'message' => 'Coupon applied successfully',
            'discount' => $discount,
            'discounted_total' => $discounted_total,
        ]);
    }
}
