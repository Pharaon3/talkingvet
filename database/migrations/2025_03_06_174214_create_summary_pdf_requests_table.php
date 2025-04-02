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
        Schema::create('summary_pdf_requests', function (Blueprint $table) {
            $table->id();
            $table->string('encounter_id')->nullable();
            $table->string('pdf_location')->nullable();
            $table->longText('parsed_text')->nullable();
            $table->integer('state')->nullable();
            $table->boolean('valid')->nullable();
            $table->integer('retries')->nullable();
            $table->integer('error')->nullable();
            $table->string('error_msg')->nullable();
            $table->dateTime('submit_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('summarize_pdf_requests');
    }
};
