<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResearchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('researches', function (Blueprint $table) {
            $table->id(); 
            $table->string('title'); 
            $table->unsignedBigInteger('field_id'); 
            $table->unsignedBigInteger('instructor_id'); 
            $table->string('link')->nullable(); 
            $table->text('description')->nullable(); 
            $table->timestamps(); 

            $table->foreign('field_id')->references('id')->on('fields')->onDelete('cascade');
            $table->foreign('instructor_id')->references('id')->on('instructors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('researches');
    }
}
