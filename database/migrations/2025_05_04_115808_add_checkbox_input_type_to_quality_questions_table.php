<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddCheckboxInputTypeToQualityQuestionsTable extends Migration
{
    public function up()
    {
        Schema::table('quality_questions', function (Blueprint $table) {
            // Modify the enum column to include 'checkbox' type
            DB::statement("ALTER TABLE quality_questions MODIFY COLUMN input_type ENUM('number', 'dropdown', 'textarea', 'text', 'checkbox')");
        });
    }

    public function down()
    {
        Schema::table('quality_questions', function (Blueprint $table) {
            // Revert back to previous enum values
            DB::statement("ALTER TABLE quality_questions MODIFY COLUMN input_type ENUM('number', 'dropdown', 'textarea', 'text')");
        });
    }
}