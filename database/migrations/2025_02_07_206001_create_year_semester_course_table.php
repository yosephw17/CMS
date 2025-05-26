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
        Schema::create('year_semester_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('year_id')->constrained('years')->onDelete('cascade'); 
            $table->foreignId('semester_id')->constrained('semesters')->onDelete('cascade'); 
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');

            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->foreignId('stream_id')->nullable()->constrained('streams')->onDelete('cascade');

            $table->unsignedBigInteger('preferred_lecture_room_id')->nullable();
            $table->unsignedBigInteger('preferred_lab_room_id')->nullable();
            $table->timestamps();

            $table->foreign('preferred_lecture_room_id')->references('id')->on('rooms');
    $table->foreign('preferred_lab_room_id')->references('id')->on('rooms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('year_semester_course');
    }
};
