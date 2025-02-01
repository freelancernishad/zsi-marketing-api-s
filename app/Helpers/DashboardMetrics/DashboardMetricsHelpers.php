<?php

use Carbon\Carbon;
use App\Models\Package;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;


function getPackageRevenueData($year = null, $week = 'current')
{
    $year = $year ?? now()->year; // Default to current year

    $monthlyResult = [];
    $totalRevenueByPackage = [];
    $totalRevenueByPackageYearly = [];
    $totalRevenueByPackageWeekly = [];

    // Private package tracking
    $privateTotalRevenue = 0;
    $privateTotalRevenueYearly = 0;
    $privateWeeklyData = array_fill(0, 7, 0); // Weekly revenue for private packages

    // Define the week range based on the provided $week parameter
    if ($week === 'current') {
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
    } elseif ($week === 'last') {
        $weekStart = Carbon::now()->subWeek()->startOfWeek();
        $weekEnd = Carbon::now()->subWeek()->endOfWeek();
    }

    $packages = Package::where('type', 'public')->get(); // Only public packages
    foreach ($packages as $package) {
        $monthlyData = array_fill(0, 12, 0);

        // Monthly Payments
        $payments = Payment::select(
                DB::raw('MONTH(paid_at) as month'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->where('payable_type', 'App\\Models\\Package')
            ->where('payable_id', $package->id)
            ->completed()
            ->whereYear('paid_at', $year)
            ->groupBy(DB::raw('MONTH(paid_at)'))
            ->get();

        foreach ($payments as $payment) {
            $monthlyData[$payment->month - 1] = (int) $payment->total_amount;
        }

        $totalRevenue = array_sum($monthlyData);

        $monthlyResult[] = [
            'name' => $package->name,
            'data' => $monthlyData,
        ];

        // Yearly Payments
        $yearlyRevenue = Payment::where('payable_type', 'App\\Models\\Package')
            ->where('payable_id', $package->id)
            ->completed()
            ->whereYear('paid_at', $year)
            ->sum('amount');

        // Weekly Payments
        $weeklyData = array_fill(0, 7, 0);
        $weeklyPayments = Payment::select(
                DB::raw('DAYOFWEEK(paid_at) as day'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->where('payable_type', 'App\\Models\\Package')
            ->where('payable_id', $package->id)
            ->completed()
            ->whereBetween('paid_at', [$weekStart, $weekEnd])
            ->groupBy(DB::raw('DAYOFWEEK(paid_at)'))
            ->get();

        foreach ($weeklyPayments as $payment) {
            $weeklyData[$payment->day - 1] = (int) $payment->total_amount;
        }

        // Public package revenue aggregation
        $totalRevenueByPackage[] = [
            'name' => $package->name,
            'total_revenue' => $totalRevenue,
        ];

        $totalRevenueByPackageYearly[] = [
            'name' => $package->name,
            'total_revenue_yearly' => (int) $yearlyRevenue,
        ];

        $totalRevenueByPackageWeekly[] = [
            'name' => $package->name,
            'data' => $weeklyData,
        ];
    }

    // **New Separate Loop for Private Packages**
    $privatePackages = Package::where('type', 'private')->get();
    foreach ($privatePackages as $package) {
        $totalRevenue = Payment::where('payable_type', 'App\\Models\\Package')
            ->where('payable_id', $package->id)
            ->completed()
            ->whereYear('paid_at', $year)
            ->sum('amount');

        $yearlyRevenue = Payment::where('payable_type', 'App\\Models\\Package')
            ->where('payable_id', $package->id)
            ->completed()
            ->whereYear('paid_at', $year)
            ->sum('amount');

        $weeklyPayments = Payment::select(
                DB::raw('DAYOFWEEK(paid_at) as day'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->where('payable_type', 'App\\Models\\Package')
            ->where('payable_id', $package->id)
            ->completed()
            ->whereBetween('paid_at', [$weekStart, $weekEnd])
            ->groupBy(DB::raw('DAYOFWEEK(paid_at)'))
            ->get();

        foreach ($weeklyPayments as $payment) {
            $privateWeeklyData[$payment->day - 1] += (int) $payment->total_amount;
        }

        $privateTotalRevenue += $totalRevenue;
        $privateTotalRevenueYearly += $yearlyRevenue;
    }

    // Add combined private package revenue under "Private Package"
    if ($privateTotalRevenue > 0) {
        $totalRevenueByPackage[] = [
            'name' => 'Private Package',
            'total_revenue' => $privateTotalRevenue
        ];
    }

    if ($privateTotalRevenueYearly > 0) {
        $totalRevenueByPackageYearly[] = [
            'name' => 'Private Package',
            'total_revenue_yearly' => $privateTotalRevenueYearly
        ];
    }

    if (array_sum($privateWeeklyData) > 0) {
        $totalRevenueByPackageWeekly[] = [
            'name' => 'Private Package',
            'data' => $privateWeeklyData
        ];
    }

    // Handle empty array for max function
    $maxMonthlyRevenue = !empty($totalRevenueByPackage) ? max(array_column($totalRevenueByPackage, 'total_revenue')) : 0;
    $maxWeeklyRevenue = !empty($totalRevenueByPackageWeekly) ? max(array_map(fn($item) => max($item['data']), $totalRevenueByPackageWeekly)) : 0;

    return [
        'monthly_package_revenue' => $monthlyResult,
        'monthly_package_revenue_max' => getDynamicMaxValue($maxMonthlyRevenue),
        'total_revenue_per_package' => $totalRevenueByPackage,
        'yearly_package_revenue' => $totalRevenueByPackageYearly,
        'weekly_package_revenue' => $totalRevenueByPackageWeekly,
        'weekly_package_revenue_max' => getDynamicMaxValue($maxWeeklyRevenue),
    ];
}






function getDynamicMaxValue($value)
{
    // If the value is less than or equal to 0, return 0
    if ($value <= 0) {
        return 0;
    }

    // Determine the number of digits in the value
    $digitCount = strlen((string)$value);

    // Calculate the base scale dynamically based on the digit count
    $baseScale = 10 ** ($digitCount - 1); // Example: For 3 digits, baseScale = 100 (10^2)

    // For 1 and 2 digits, we set a minimum scaling factor of 100
    if ($digitCount < 3) {
        $baseScale = 100; // Minimum base scale for 1 or 2 digits
    }

    // Calculate the next max value based on the scaling factor
    $maxValue = ceil($value / $baseScale) * $baseScale;

    // Ensure the maxValue is at least the original value
    if ($maxValue < $value) {
        $maxValue += $baseScale;
    }

    return $maxValue;
}
