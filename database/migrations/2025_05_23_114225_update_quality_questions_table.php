<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateQualityQuestionsTable extends Migration
{
    public function up()
    {
        Schema::table('quality_questions', function (Blueprint $table) {
            $table->enum('audience', ['instructor', 'student'])->after('input_type');
        });
    }

    public function down()
    {
        Schema::table('quality_questions', function (Blueprint $table) {
            $table->dropColumn('audience');
        });
    }
}