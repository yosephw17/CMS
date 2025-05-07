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
        Schema::create('streams', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Power Engineering", "Control Systems"
        
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
        
            $table->foreignId('year_id')->constrained();
            $table->foreignId('semester_id')->constrained();
        
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('streams');
    }
};
