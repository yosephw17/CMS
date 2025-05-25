<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQualityLinksTable extends Migration
{
    public function up()
    {
        Schema::create('quality_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_session_id')->constrained('audit_sessions');
            $table->foreignId('instructor_id')->constrained('instructors')->onDelete('cascade'); // Assuming instructors are in 'users' table
            $table->foreignId('semester_id')->constrained('semesters')->onDelete('cascade'); // Assuming semesters table exists
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade'); // Assuming academic_years table exists
            $table->string('hash', 40)->unique();
            $table->boolean('is_used')->default(false);
            $table->timestamps();

            // Optional: Add index for faster lookups
            $table->index(['hash', 'is_used']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('quality_links');
    }
}