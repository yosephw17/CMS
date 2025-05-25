<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterQualityLinksInstructorIdForeignKey extends Migration
{
    public function up()
    {
        Schema::table('quality_links', function (Blueprint $table) {
            // Drop existing foreign key
            $table->dropForeign(['instructor_id']);
            // Add new foreign key with cascade
            $table->foreign('instructor_id')
                  ->references('id')
                  ->on('instructors')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('quality_links', function (Blueprint $table) {
            $table->dropForeign(['instructor_id']);
            $table->foreign('instructor_id')
                  ->references('id')
                  ->on('instructors');
        });
    }
}