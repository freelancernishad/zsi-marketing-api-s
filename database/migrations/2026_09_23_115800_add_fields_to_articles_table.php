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
        Schema::table('articles', function (Blueprint $table) {
            $table->text('excerpt')->nullable()->after('content');
            $table->string('author')->nullable()->after('excerpt');
            $table->date('date')->nullable()->after('author');
            $table->string('readTime')->nullable()->after('date');
            $table->json('keyTakeaways')->nullable()->after('readTime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['excerpt', 'author', 'date', 'readTime', 'keyTakeaways']);
        });
    }
};
