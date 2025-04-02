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
        Schema::create('summary_results', function (Blueprint $table) {
            $table->id();
            $table->string('summarizationID');
            $table->string('username', 50);
            $table->string('prompt');
            $table->string('source');
            $table->string('output');
            $table->double('input_tokens');
            $table->double('output_tokens');
            $table->timestamp('summary_date')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('summary_results');
    }
};