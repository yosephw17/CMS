<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQualityQuestionsTable extends Migration
{
    public function up()
    {
        Schema::create('quality_questions', function (Blueprint $table) {
            $table->id();
            $table->text('question_text'); // e.g., "Total Number of Chapters Covered"
            $table->enum('input_type', ['number', 'dropdown', 'textarea']); // Field type
            $table->json('options')->nullable(); // Only used for 'dropdown' type
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('quality_questions');
    }
}