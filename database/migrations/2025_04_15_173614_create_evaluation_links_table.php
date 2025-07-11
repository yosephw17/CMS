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
        // Evaluation links (replaces invitations)
Schema::create('evaluation_links', function (Blueprint $table) {
    $table->id();
    $table->foreignId('instructor_id')->constrained('instructors')->onDelete('cascade');
    $table->string('student_email');
    $table->string('hash')->unique(); // URL identifier
    $table->boolean('is_used')->default(false);
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_links');
    }
};
