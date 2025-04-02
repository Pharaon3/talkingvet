<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /*
     * {
        "id": 1,
        "encounterID": "202502120001",
        ...,
        "transcripts": [
            {"id: 0",
            "transcript":"This is one transcript",
            "date_created": 202501020011
            },
            {"id: 1",
            "transcript":"This is another transcript",
            "date_created": 202501020012
            }
        ]}
     * */
    public function up(): void
    {
        Schema::create('encounters', function (Blueprint $table) {
            $table->id();
            $table->string('encounter_id');//encounter id in doc file
            $table->foreignId('organization_id')->constrained('organizations');
            $table->integer('default_prompt_id');
            $table->integer('created_by');
            $table->string('identifier');
            $table->longText('notes');
            $table->date('encounter_date');
            $table->longText('transcripts');
            $table->longText('summary');
            $table->longText('history_summary');
            $table->integer('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encounters');
    }
};
