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
        // Responses
Schema::create('evaluation_responses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('link_id')->constrained('evaluation_links');
    $table->foreignId('question_id')->constrained('evaluation_questions');
    $table->unsignedTinyInteger('rating'); // 0-5
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_responses');
    }
};
