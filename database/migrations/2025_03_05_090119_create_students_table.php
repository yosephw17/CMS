<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('full_name'); // Replacing 'name' with 'full_name'
            $table->string('sex');
            $table->string('phone_number')->unique();
            $table->string('hosting_company')->nullable();
            $table->string('location')->nullable();
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('assigned_mentor_id')->nullable();
            $table->foreign('assigned_mentor_id')->references('id')->on('instructors')->onDelete('set null');
            $table->timestamps();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');

        });
    }

    public function down()
    {
        Schema::dropIfExists('students');
    }
};
