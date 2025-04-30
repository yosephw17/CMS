<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('evaluation_links', function (Blueprint $table) {
            // Add academic_year_id (nullable first if needed)
            $table->foreignId('academic_year_id')
                  ->after('instructor_id')
                  ->constrained();

            // Add semester_id (nullable first if needed)
            $table->foreignId('semester_id')
                  ->after('academic_year_id')
                  ->constrained();
        });
    }

    public function down()
    {
        Schema::table('evaluation_links', function (Blueprint $table) {
            $table->dropConstrainedForeignId('academic_year_id');
            $table->dropConstrainedForeignId('semester_id');
        });
    }
};