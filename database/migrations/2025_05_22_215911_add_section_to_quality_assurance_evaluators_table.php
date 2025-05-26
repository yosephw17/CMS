<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('quality_assurance_evaluators', function (Blueprint $table) {
            $table->string('section')
                ->after('email')
                ->nullable() // Remove if section is required
                ->comment('The class/section being evaluated');
        });
    }

    public function down()
    {
        Schema::table('quality_assurance_evaluators', function (Blueprint $table) {
            $table->dropColumn('section');
        });
    }
};
