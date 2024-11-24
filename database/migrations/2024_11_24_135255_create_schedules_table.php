<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchedulesTable extends Migration
{
    public function up()
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('assigned_to');
            $table->timestamp('event_start_time')->useCurrent();
            $table->timestamp('event_end_time')->useCurrent();
            $table->string('invitee_first_name');
            $table->string('invitee_last_name');
            $table->string('invitee_full_name')->nullable();
            $table->string('invitee_email');
            $table->json('answers'); // Store answers as a JSON array
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); // user who created the schedule
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('cascade'); // admin who created or is managing the schedule
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('schedules');
    }
}
