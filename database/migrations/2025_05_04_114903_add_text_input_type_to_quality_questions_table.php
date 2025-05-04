<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTextInputTypeToQualityQuestionsTable extends Migration
{
    public function up()
    {
        Schema::table('quality_questions', function (Blueprint $table) {
            // First modify the enum column to include 'text' type
            DB::statement("ALTER TABLE quality_questions MODIFY COLUMN input_type ENUM('number', 'dropdown', 'textarea', 'text')");
        });
    }

    public function down()
    {
        Schema::table('quality_questions', function (Blueprint $table) {
            // Revert back to original enum values if needed
            DB::statement("ALTER TABLE quality_questions MODIFY COLUMN input_type ENUM('number', 'dropdown', 'textarea')");
        });
    }
}