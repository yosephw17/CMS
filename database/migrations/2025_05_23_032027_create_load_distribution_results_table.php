<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('load_distribution_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('load_distribution_id')->constrained()->onDelete('cascade');
            $table->foreignId('instructor_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('course_id')->nullable()->constrained()->onDelete('set null');
            $table->string('section')->nullable();
            $table->integer('students_count')->default(0);
            $table->string('assignment_type')->default('lecture');
            $table->float('lecture_hours')->default(0);
            $table->float('lab_hours')->default(0);
            $table->float('tutorial_hours')->default(0);
            $table->integer('lecture_sections')->default(0);
            $table->integer('lab_sections')->default(0);
            $table->integer('tutorial_sections')->default(0);
            $table->float('elh')->default(0);
            $table->float('total_load')->default(0);
            $table->float('over_under_load')->default(0);
            $table->float('amount_paid')->default(0);
            $table->float('expected_load')->default(0); // Add this line
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('load_distribution_results');
    }
};