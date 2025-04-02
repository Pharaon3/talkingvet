<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Drop the old users table if it exists
        Schema::dropIfExists('users');

        // Create the new users table
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique()->collation('utf8mb4_general_ci'); // Case-insensitive collation            $table->string('server');
            $table->string('password');
            $table->string('firstname');
            $table->string('lastname');
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->integer('login_server');//0-USA, 1-CANADA, 2-TEST
            $table->string('default_language')->default('en-us');
            $table->boolean('enabled')->default(true);
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
