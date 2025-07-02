<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddYearsOfExperienceToCareersJobsTable extends Migration
{
    public function up()
    {
        Schema::table('careers_jobs', function (Blueprint $table) {
            $table->string('years_of_experience')->after('experience_level')->nullable();
        });
    }

    public function down()
    {
        Schema::table('careers_jobs', function (Blueprint $table) {
            $table->dropColumn('years_of_experience');
        });
    }
}
