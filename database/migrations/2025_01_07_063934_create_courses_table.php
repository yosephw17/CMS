<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id(); 
            $table->string('name'); 
            $table->string('course_code')->unique();
            $table->integer('cp'); 
            $table->integer('lecture_cp')->default(0); 
            $table->integer('lab_cp')->default(0); 
            $table->unsignedBigInteger('department_id');

            $table->timestamps(); 

            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('courses');
    }
}
