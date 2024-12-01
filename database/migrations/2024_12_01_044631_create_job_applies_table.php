<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobAppliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_applies', function (Blueprint $table) {
            $table->id();
            $table->string('full_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->longText('cover_letter')->nullable();
            $table->string('resume')->nullable(); // File path for resume

            // CareersJob fields replicated
            $table->string('job_title');
            $table->longText('job_details')->nullable();
            $table->longText('responsibilities')->nullable();
            $table->integer('vacancies')->nullable();
            $table->string('job_type')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('category')->nullable();
            $table->string('employment_type')->nullable();
            $table->string('experience_level')->nullable();
            $table->string('salary_type')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->string('office_time')->nullable();
            $table->boolean('show_on_career_page')->default(true);
            $table->string('requested_origin')->nullable();
            $table->string('application_id')->unique();
            $table->foreignId('careers_job_id')->unique()->constrained('careers_jobs')->onDelete('cascade'); // Add foreign key for CareersJob

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_applies');
    }
}
