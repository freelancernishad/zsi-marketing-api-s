<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToUserPackageDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_package_documents', function (Blueprint $table) {
            // Add the user_id column as a foreign key
            $table->unsignedBigInteger('user_id')->after('userpackage_id');

            // Add foreign key constraint
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade'); // Optional: cascade delete if the user is deleted
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_package_documents', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['user_id']);

            // Drop the user_id column
            $table->dropColumn('user_id');
        });
    }
}
