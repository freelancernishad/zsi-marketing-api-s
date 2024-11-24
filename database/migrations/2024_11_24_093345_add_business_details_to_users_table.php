<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('business_name')->nullable()->after('phone');
            $table->string('country')->nullable()->after('business_name');
            $table->string('state')->nullable()->after('country');
            $table->string('city')->nullable()->after('state');
            $table->string('region')->nullable()->after('city');
            $table->string('zip_code')->nullable()->after('region');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'business_name', 'country', 'state', 'city', 'region', 'zip_code']);
        });
    }

};
