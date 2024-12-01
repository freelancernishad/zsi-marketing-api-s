<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('careers_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_title')->nullable();
            $table->text('job_details')->nullable();
            $table->text('responsibilities')->nullable();
            $table->integer('vacancies')->nullable();
            $table->string('job_type')->nullable(); // e.g., full-time, part-time
            $table->date('expiry_date')->nullable();
            $table->string('category')->nullable();
            $table->string('employment_type')->nullable(); // e.g., permanent, temporary
            $table->string('experience_level')->nullable(); // e.g., entry, mid, senior
            $table->string('salary_type')->nullable(); // e.g., negotiable, fixed
            $table->decimal('salary', 10, 2)->nullable()->default(0);
            $table->string('office_time')->nullable();
            $table->boolean('show_on_career_page')->nullable()->default(false);
            $table->string('requested_origin')->nullable(); // Field for storing request origin
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('careers_jobs');
    }
};
