<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->integer('lab_cp')->default(0)->change(); // Set default to 0
        });

      
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->integer('lab_cp')->change(); // Remove default (if needed)
        });

  
    }
};
