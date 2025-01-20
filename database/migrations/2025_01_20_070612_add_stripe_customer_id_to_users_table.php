<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStripeCustomerIdToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Add the stripe_customer_id column
            $table->string('stripe_customer_id')
                  ->nullable()
                  ->after('email') // Add the column after the 'email' column
                  ->comment('Stripe customer ID for recurring payments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the stripe_customer_id column
            $table->dropColumn('stripe_customer_id');
        });
    }
}
