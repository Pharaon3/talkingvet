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
        // Drop the old users table if it exists
        // Schema::dropIfExists('users');

        Schema::table('users', function($table) {
            $table->boolean('sync_needed')->nullable(true)->after('remember_token');
            $table->string('sync_key')->nullable(true)->after('sync_needed');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('users', function($table) {
            $table->dropColumn('sync_needed');
            $table->dropColumn('sync_key');
        });
    }
};
