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
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('preferred_lecture_room_id')->nullable();
            $table->unsignedBigInteger('preferred_lab_room_id')->nullable();


            $table->timestamps(); 

            $table->foreign('preferred_lecture_room_id')->references('id')->on('rooms')->onDelete('set null');
            $table->foreign('preferred_lab_room_id')->references('id')->on('rooms')->onDelete('set null');


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
