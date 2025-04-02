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
        Schema::create('gen_ai_internal_requests', function (Blueprint $table) {
            $table->id();
            $table->string('encounter_id');
            $table->string('assembly_ai_job_id');
            $table->string('audio_location');
            $table->string('local_audio_file');
            $table->string('audio_url');
            $table->integer('state');
            $table->boolean('valid');
            $table->integer('retries');
            $table->integer('error');
            $table->string('error_msg');
            $table->dateTime('submit_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gen_ai_internal_requests');
    }
};
