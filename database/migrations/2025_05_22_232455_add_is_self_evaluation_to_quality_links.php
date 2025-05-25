<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('quality_links', function (Blueprint $table) {
            $table->unsignedBigInteger('evaluator_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('quality_links', function (Blueprint $table) {
            $table->unsignedBigInteger('evaluator_id')->nullable(false)->change();
        });
    }
};
