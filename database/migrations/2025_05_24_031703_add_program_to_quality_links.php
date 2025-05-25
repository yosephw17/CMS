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
        Schema::table('quality_links', function (Blueprint $table) {
            $table->unsignedBigInteger('program')->nullable()->after('section');
            $table->foreign('program')
                  ->references('id')
                  ->on('departments')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quality_links', function (Blueprint $table) {
            $table->dropForeign(['program']);
            $table->dropColumn('program');
        });
    }
};