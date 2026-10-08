<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('custom_service_payments', function (Blueprint $table) {
            $table->id();
            $table->string('service_type'); // repairing, maintenance, others, custom
            $table->string('service_name'); // e.g. "Matribhumi Maintenance", "Laptop Repairing", or custom text
            $table->enum('payment_type', ['one_time', 'recurring'])->default('one_time');
            $table->string('billing_cycle')->nullable()->default('monthly'); // monthly, yearly
            $table->decimal('amount', 12, 2);
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->string('status')->default('pending'); // pending, completed, processing, cancelled
            $table->string('payment_method')->default('stripe');
            $table->string('transaction_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_service_payments');
    }
};
