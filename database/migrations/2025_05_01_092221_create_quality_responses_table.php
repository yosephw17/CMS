<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQualityResponsesTable extends Migration
{
    public function up()
    {
        Schema::create('quality_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quality_link_id')->constrained('quality_links')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('quality_questions');
            $table->text('answer'); // Stores numbers, text, or JSON
            $table->timestamps();

            // Prevent duplicate answers for same question/link combo
            $table->unique(['quality_link_id', 'question_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('quality_responses');
    }
}