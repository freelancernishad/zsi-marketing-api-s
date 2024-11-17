<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTokenBlacklistsTable extends Migration
{
    public function up()
    {
        Schema::create('token_blacklists', function (Blueprint $table) {
            $table->id();
            $table->string('token', 65535);  // Change to VARCHAR(65535) if appropriate
            $table->unique('token');         // Create a unique index
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('token_blacklists');
    }
}
