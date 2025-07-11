<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('evaluators', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('name');
            $table->string('type'); // Consider using enum() if your DB supports it
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index('email');
            $table->index('type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('evaluators');
    }
};
