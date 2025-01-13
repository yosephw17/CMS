<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstructorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instructors', function (Blueprint $table) {
            $table->id(); 
            $table->string('name'); 
            $table->string('email')->unique(); 
            $table->string('phone')->nullable(); 
            $table->unsignedBigInteger('role_id');
            $table->boolean('is_available')->default(true); 
            $table->boolean('is_studying')->default(false); 
            $table->boolean('is_approved')->default(false); 
            $table->timestamps(); 

            $table->foreign('role_id')->references('id')->on('instructor_roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('instructors');
    }
}
