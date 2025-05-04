<?php

// database/migrations/YYYY_MM_DD_update_evaluation_links_table_for_evaluators.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('evaluation_links', function (Blueprint $table) {
            // Remove old columns
            $table->dropColumn(['student_email', 'student_name']);

            // Add new evaluator relationship
            $table->foreignId('evaluator_id')->constrained('evaluators');

            // Optional but recommended
            $table->timestamp('completed_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('evaluation_links', function (Blueprint $table) {
            $table->string('student_email');
            $table->string('student_name');
            $table->dropForeign(['evaluator_id']);
            $table->dropColumn(['evaluator_id', 'completed_at']);
        });
    }
};