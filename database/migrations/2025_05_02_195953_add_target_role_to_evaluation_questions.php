<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('evaluation_questions', function (Blueprint $table) {
            $table->string('target_role')
                  ->default('all')
                  ->after('type')
                  ->comment('lab_assistant, regular_instructor');
        });
    }

    public function down()
    {
        Schema::table('evaluation_questions', function (Blueprint $table) {
            $table->dropColumn('target_role');
        });
    }
};