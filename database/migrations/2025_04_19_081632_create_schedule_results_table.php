<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('schedule_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('instructor_id')->nullable();
            $table->unsignedBigInteger('guest_instructor_id')->nullable();

            $table->unsignedBigInteger('schedule_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('room_id');
            $table->foreignId('stream_id')->nullable()->constrained()->onDelete('cascade'); // NULL for non-stream departments

            $table->timestamps();

            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('instructor_id')->references('id')->on('instructors')->onDelete('cascade');
            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade');
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
            $table->foreign('guest_instructor_id')->references('id')->on('guest_instructors')->onDelete('set null');

        });
    }

    public function down(): void {
        Schema::dropIfExists('schedule_results');
    }
};
