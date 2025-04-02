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
        Schema::create('logintokens', function (Blueprint $table) {
            $table->id();
            $table->string('token');
            $table->string('scheme');
            $table->string('base64_auth');
            $table->string('country');
            $table->integer('used');
            $table->timestamp('creation_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logintokens');
    }
};
