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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->string('year');
            $table->foreignId('semester_id')->constrained('semesters')->onDelete('cascade'); 
            $table->unsignedBigInteger('department_id');
            $table->foreignId('stream_id')->nullable()->constrained()->onDelete('cascade'); // NULL for non-stream departments

            $table->timestamps();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
