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
        Schema::create('gen_ai_requests', function (Blueprint $table) {
            $table->string('trxId')->primary();
            $table->string('assemblyAIJobId');
            $table->string('audioLocation');
            $table->string('localAudioFile');
            $table->string('audioUrl');
            $table->string('country');
//            $table->string('token');
            $table->string('username')->nullable();
            $table->string('userAuthString');
            $table->integer('state');
            $table->boolean('valid');
            $table->integer('retries');
            $table->integer('error');
            $table->string('error_msg');
            $table->dateTime('submitTime');

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gen_ai_requests');
    }
};
