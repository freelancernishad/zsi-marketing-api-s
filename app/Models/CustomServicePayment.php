<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomServicePayment extends Model
{
    use HasFactory;

    protected $table = 'custom_service_payments';

    protected $fillable = [
        'service_type',
        'service_name',
        'payment_type',
        'billing_cycle',
        'amount',
        'customer_name',
        'customer_email',
        'customer_phone',
        'status',
        'payment_method',
        'transaction_id',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
