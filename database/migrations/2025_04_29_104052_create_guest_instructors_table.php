<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuestInstructorsTable extends Migration
{
    public function up()
    {
        Schema::create('guest_instructors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('schedule_id');
            $table->unsignedBigInteger('course_id');
            $table->timestamps();

            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('guest_instructors');
    }
}
