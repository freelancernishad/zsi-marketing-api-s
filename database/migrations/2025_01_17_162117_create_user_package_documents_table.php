<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPackageDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_package_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userpackage_id'); // Foreign key to UserPackage
            $table->date('uploaded_date'); // Date when the document/report was uploaded
            $table->string('file'); // Path to the uploaded file
            $table->enum('type', ['document', 'report']); // Type: document or report
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('userpackage_id')
                  ->references('id')
                  ->on('user_packages')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_package_documents');
    }
}
