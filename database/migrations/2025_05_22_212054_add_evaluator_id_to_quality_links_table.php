<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('quality_links', function (Blueprint $table) {
            $table->foreignId('evaluator_id')
                  ->nullable()
                  ->after('instructor_id')
                  ->constrained('quality_assurance_evaluators')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('quality_links', function (Blueprint $table) {
            $table->dropForeign(['evaluator_id']);
            $table->dropColumn('evaluator_id');
        });
    }
};
