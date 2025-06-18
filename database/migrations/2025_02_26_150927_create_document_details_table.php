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
        Schema::create('documents_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doc_id');
            $table->text('stats');
            $table->integer('rank');
            $table->timestamps();

            // Foreign Key Constraint
            $table->foreign('doc_id')->references('id')->on('documents')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents_details');
    }
};
