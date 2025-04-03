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
        Schema::table('instructor_educational_background', function (Blueprint $table) {
            $table->boolean('isApproved')->default(false)->after('field_id'); // Adding the column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instructor_educational_background', function (Blueprint $table) {
            $table->dropColumn('isApproved');
        });
    }
};
