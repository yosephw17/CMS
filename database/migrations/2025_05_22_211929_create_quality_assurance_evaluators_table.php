<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('quality_assurance_evaluators', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->foreignId('instructor_id')->constrained('instructors')->onDelete('cascade');
            $table->foreignId('semester_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->foreignId('audit_session_id')->constrained('audit_sessions')->onDelete('cascade');
            $table->timestamps();

            // Fixed index with shorter name
            $table->index(
                ['email', 'instructor_id', 'semester_id'],
                'qae_email_instr_sem_idx'
            );
        });
    }

    public function down()
    {
        Schema::dropIfExists('quality_assurance_evaluators');
    }
};
