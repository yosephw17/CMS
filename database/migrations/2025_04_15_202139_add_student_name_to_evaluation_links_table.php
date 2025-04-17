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
    Schema::table('evaluation_links', function (Blueprint $table) {
        $table->string('student_name')->after('student_email');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down()
{
    Schema::table('evaluation_links', function (Blueprint $table) {
        $table->dropColumn('student_name');
    });
}

};
