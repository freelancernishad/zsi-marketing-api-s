<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeBusinessNameColumnToJsonInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Ensure all existing values are valid JSON or set a default value
            DB::statement('UPDATE users SET business_name = "[]" WHERE JSON_VALID(business_name) = 0 OR business_name IS NULL');
        });

        Schema::table('users', function (Blueprint $table) {
            // Modify the column to JSON type
            DB::statement('ALTER TABLE users MODIFY COLUMN business_name JSON NULL');
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
            // Revert the column back to string type
            DB::statement('ALTER TABLE users MODIFY COLUMN business_name TEXT NULL');
        });
    }
}
