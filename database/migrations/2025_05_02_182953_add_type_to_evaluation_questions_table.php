<?php

// In the generated migration file
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('evaluation_questions', function (Blueprint $table) {
            $table->string('type')->default('general')->after('order');
            // Options: general, student, instructor, dean
        });
    }

    public function down()
    {
        Schema::table('evaluation_questions', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
