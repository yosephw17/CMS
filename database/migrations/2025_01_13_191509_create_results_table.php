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
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instructor_id');
            $table->unsignedBigInteger('previous_instructor_id')->nullable(); // Removed ->after()
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('assignment_id');
            $table->integer('point')->default(0);
            $table->boolean('is_assigned')->default(false);
            $table->longText('reason')->nullable();
            $table->foreignId('stream_id')->nullable()->constrained('streams')->onDelete('cascade');

            $table->timestamps();

            $table->foreign('instructor_id')->references('id')->on('instructors')->onDelete('cascade');
            $table->foreign('previous_instructor_id')->references('id')->on('instructors')->onDelete('cascade'); // Fixed column name
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('assignment_id')->references('id')->on('assignments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};