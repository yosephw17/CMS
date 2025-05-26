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
            $table->boolean('is_self_evaluation')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
{
    Schema::table('quality_links', function (Blueprint $table) {
        $table->dropColumn('is_self_evaluation');
    });
}
};
