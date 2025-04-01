<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropDepartmentIdFromInstructorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('instructors', function (Blueprint $table) {
            $table->dropForeign(['department_id']); // Drop foreign key constraint
            $table->dropColumn('department_id'); // Drop the column
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('instructors', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->after('role_id'); // Add the column back
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade'); // Restore foreign key
        });
    }
}
