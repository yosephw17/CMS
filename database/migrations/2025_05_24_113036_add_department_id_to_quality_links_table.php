<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('quality_links', function (Blueprint $table) {
            // Add the department_id column as foreign key
            $table->unsignedBigInteger('department_id')->nullable()->after('section');

            // Add foreign key constraint
            $table->foreign('department_id')
                  ->references('id')
                  ->on('departments')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('quality_links', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};